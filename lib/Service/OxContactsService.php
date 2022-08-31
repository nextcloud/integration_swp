<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021 Julien Veyssier <eneiluj@posteo.net>
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 *
 * Ox Integration
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\SpsBmi\Service;

use Exception;
use OCA\SpsBmi\AppInfo\Application;
use OCA\SpsBmi\Exception\ServiceException;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use Psr\Log\LoggerInterface;
use Throwable;

class OxContactsService extends OxBaseService {

	/** @var IClientService */
	private $clientService;
	/** @var LoggerInterface */
	private $logger;
	/** @var string|null */
	private $userId;

	public function __construct(IConfig $config,
								TokenService $tokenService,
								IClientService $clientService,
								LoggerInterface $logger,
								?string $userId) {
		parent::__construct($config, $tokenService, $logger, $userId);
		$this->clientService = $clientService;
		$this->logger = $logger;
		$this->userId = $userId;
	}

	/**
	 * @throws ServiceException
	 */
	public function search(string $searchTerm, array $options) {
		if (!$this->checkSetup()) {
			return [];
		}
		//$searchTerm = '*' . str_replace(' ', '*,*', $searchTerm) . '*';
		$searchTerm = '*' . $searchTerm . '*';
		// TODO choose between /contacts?action=autocomplete and /contacts?action=search endpoints
		// documentation: https://documentation.open-xchange.com/components/middleware/http/latest/index.html#!Contacts

		// search (PUT)
		$searchUrl = $this->getOxBaseUrl('/api/contacts');
		$getParams = [
			'action' => 'search',
			// object ID, last_modified, display name, email 1, 2 and 3
			'columns' => '1,5,500,555,556,557,524',
			'sort' => '5',
			'order' => 'desc',
		];
		$paramsContent = http_build_query($getParams);
		$searchUrl .= '?' . $paramsContent;
		$requestBody = [
			'orSearch' => true,
			'pattern' => $searchTerm,
			'email1' => $searchTerm,
			'email2' => $searchTerm,
			'email3' => $searchTerm,
		];

		//$searchUrl = $this->getOxBaseUrl('/rest/contacts/v1') . '/query?filter=or(is(first,any(' .$searchTerm .')),is(last,any('.$searchTerm.')),is(emails.*.email,any('.$searchTerm.')))&fields=take(first,last,emails)';
		if (isset($options['limit'])) {
			$searchUrl .= '&count=' . $options['limit'];
		}
		try {
			$client = $this->clientService->newClient();
			$requestOptions = $this->getOxOptions();
			$requestOptions['body'] = json_encode($requestBody);
			$response = $client->put($searchUrl, $requestOptions);
			$responseBody = $response->getBody();
			$this->logger->error('!!! Fetch contacts for user ' . $this->userId . ', BODY: ' . $responseBody, ['app' => Application::APP_ID]);
			return json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
		} catch (Exception $e) {
			$this->logger->error(
				'Failed to fetch contacts for user ' . $this->userId,
				[
					'exception' => $e,
					'app' => Application::APP_ID,
				]
			);
			throw new ServiceException('Could not fetch results');
		}
	}

	/**
	 * @param string $name
	 * @param string $emailAddress
	 * @return mixed
	 * @throws ServiceException
	 */
	public function createContact(string $name, string $emailAddress) {
		$client = $this->clientService->newClient();
		// get default OX contacts folder ID
		$getContactFolderUrl = $this->getOxBaseUrl('/api/config/folder/contacts');
		$requestOptions = $this->getOxOptions();
		try {
			$response = $client->get($getContactFolderUrl, $requestOptions);
			$responseBody = $response->getBody();
			$this->logger->error('CONTACT DEFAULT FOLDER response ' . $responseBody, ['app' => Application::APP_ID]);
			$responseArray = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
			$folderId = $responseArray['data'] ?? null;
		} catch (Exception | Throwable $e) {
			$this->logger->error(
				'Failed to get default contacts folder ID for user ' . $this->userId,
				[
					'exception' => $e,
					'app' => Application::APP_ID,
				]
			);
			throw new ServiceException('Could not fetch results');
		}
		// create (PUT)
		$createApiUrl = $this->getOxBaseUrl('/api/contacts');
		$getParams = [
			'action' => 'new',
		];
		$paramsContent = http_build_query($getParams);
		$createApiUrl .= '?' . $paramsContent;
		$requestBody = [
			'display_name' => $name,
			'email1' => $emailAddress,
			'folder_id' => $folderId,
		];

		try {
			$requestOptions = $this->getOxOptions();
			$requestOptions['body'] = json_encode($requestBody);
			$response = $client->put($createApiUrl, $requestOptions);
			$responseBody = $response->getBody();
			$this->logger->debug('contact creation response ' . $responseBody, ['app' => Application::APP_ID]);
			return json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
		} catch (\Exception | \Throwable $e) {
			$this->logger->error(
				'Failed to create contact (' . $emailAddress . ') for user ' . $this->userId,
				[
					'exception' => $e,
					'app' => Application::APP_ID,
				]
			);
			throw new ServiceException('Could not fetch results');
		}
	}
}
