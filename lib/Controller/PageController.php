<?php
/**
 * @copyright Copyright (c) 2021 Julien Veyssier <julien-nc@posteo.net>
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace OCA\Swp\Controller;

use Exception;
use OC\User\NoUserException;
use OCA\Swp\AppInfo\Application;
use OCA\Swp\Model\Token;
use OCA\Swp\Service\TokenService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;
use Throwable;

class PageController extends Controller {

	public function __construct(
		$appName,
		IRequest $request,
		private IURLGenerator $urlGenerator,
		private IRootFolder $rootFolder,
		private IConfig $config,
		private IClientService $clientService,
		private LoggerInterface $logger,
		private IL10N $l10n,
		private ?string $userId
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 * @UseSession
	 */
	public function index() {
		/** @var Token $token */
		$token = \OC::$server->get(TokenService::class)->getToken(true);
		if ($token === null) {
			return new JSONResponse([]);
		}
		return new JSONResponse([
			'token' => $token,
			'expires_in_seconds' => ($token->getCreatedAt() + $token->getExpiresIn()) - time()
		]);
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 * @return DataDisplayResponse
	 */
	public function getLogo(): DataDisplayResponse {
		$logoImageUrl = $this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_LOGO_IMAGE_URL);
		if ($logoImageUrl) {
			$client = $this->clientService->newClient();
			try {
				$logoResponse = $client->get($logoImageUrl);
				$fileContent = $logoResponse->getBody();
				$mimeType = $logoResponse->getHeader('Content-Type');
				if (is_array($mimeType) && count($mimeType) > 0) {
					$mimeType = $mimeType[0];
				}
				$response = new DataDisplayResponse($fileContent, Http::STATUS_OK, ['Content-Type' => $mimeType]);
				$response->cacheFor(60 * 60);
				return $response;
			} catch (Exception | Throwable $e) {
				$this->logger->error('Failed to get logo at ' . $logoImageUrl, ['exception' => $e]);
			}
		}

		// fallback to local logo
		$fileContent = file_get_contents(__DIR__ . '/../../img/phoenix_suite_logo-Assets/SVG/phoenix_suite_logo.svg');
		$response = new DataDisplayResponse($fileContent, Http::STATUS_OK, ['Content-Type' => 'image/svg+xml']);
		$response->cacheFor(60 * 60);
		return $response;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param string $format
	 * @param string|null $directory
	 * @param string|null $name
	 * @return DataResponse|RedirectResponse
	 * @throws InvalidPathException
	 * @throws NoUserException
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	public function createDocument(string $format, ?string $directory = null, ?string $name = null) {
		if (!in_array($format, ['docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp', 'odg', 'txt', 'md'])) {
			return new DataResponse('Unsupported format', Http::STATUS_BAD_REQUEST);
		}

		$userFolder = $this->rootFolder->getUserFolder($this->userId);

		// optionally choose target directory
		if ($directory !== null && $userFolder->nodeExists($directory)) {
			$targetDir = $userFolder->get($directory);
			if (!($targetDir instanceof Folder)) {
				return new DataResponse('Target directory does not exist', Http::STATUS_BAD_REQUEST);
			}
		} else {
			$targetDir = $userFolder;
		}

		// optionally choose file name
		if ($name !== null) {
			$newFileName = $name . '.' . $format;
		} else {
			$newFileName = $this->l10n->t('New document') . '.' . $format;
		}

		$uniqueNewFileName = $newFileName;
		$counter = 1;
		while ($targetDir->nodeExists($uniqueNewFileName)) {
			$uniqueNewFileName = preg_replace('/\.' . $format . '$/', ' (' . $counter . ').' . $format, $newFileName);
			$counter++;
		}

		$newFile = $targetDir->newFile($uniqueNewFileName);

		$finalUrl = $this->urlGenerator->getAbsoluteURL(
			$this->urlGenerator->linkToRoute('files.View.showFile', ['fileid' => $newFile->getId()])
		);
		return new RedirectResponse($finalUrl);
	}
}
