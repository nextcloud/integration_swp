<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021 Julien Veyssier <eneiluj@posteo.net>
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 *
 * ox Integration
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
namespace OCA\SpsBmi;

use OCA\SpsBmi\AppInfo\Application;
use OCA\SpsBmi\Exception\ServiceException;
use OCA\SpsBmi\Service\OxContactsService;
use OCP\IAddressBook;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;

class OxAddressBook implements IAddressBook {

	/** @var IConfig */
	private $config;

	/** @var ICache */
	private $cache;

	/** @var OxContactsService */
	private $oxContactsService;

	/** @var string|null */
	private $userId;

	/**
	 * ContactController constructor.
	 *
	 * @param OxContactsService $oxContactsService
	 */
	public function __construct(IConfig $config, ICacheFactory $cacheFactory, OxContactsService $oxContactsService, $userId) {
		$this->oxContactsService = $oxContactsService;
		$this->config = $config;
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID . '_contacts');
		$this->userId = $userId;
	}

	public function getKey() {
		return 'oxAddressBook'; // I don't think we can provide this
	}

	public function getUri(): string {
		return $this->oxContactsService->getOxBaseUrl(''); // expose this or no?
	}

	public function getDisplayName() {
		return 'OX Address Book'; // translate this?
	}

	/**
	 *
	 * return array an array of contacts which are arrays of key-value-pairs
	 *  example result:
	 *  [
	 *		['id' => 0, 'FN' => 'Thomas Müller', 'EMAIL' => 'a@b.c', 'GEO' => '37.386013;-122.082932'],
	 *		['id' => 5, 'FN' => 'Thomas Tanghus', 'EMAIL' => ['d@e.f', 'g@h.i']]
	 *	]
	 * @since 5.0.0
	 */
	public function search($pattern, $searchProperties, $options) {
		// use all arguments combined with the user id as a cache key
		$cacheKey = md5(json_encode([
			$this->userId, $pattern, $searchProperties, $options
		], JSON_THROW_ON_ERROR));
		$hit = $this->cache->get($cacheKey);

		if ($hit !== null) {
			return $hit;
		}
		try {
			$result = $this->oxContactsService->search($pattern, $options);
		} catch (ServiceException $e) {
			return [];
		}

		$contacts = $result['contacts'] ?? null;
		if (empty($contacts)) {
			return [];
		}
		// form the result set
		$result = array_merge(...array_map(static function ($contact) {
			$emails = $contact['emails'] ?? [];
			$template = ['FN' => ($contact['first'] ?? '') . ' ' . ($contact['last'] ?? '') ];
			if (empty($emails)) {
				return [array_merge($template, ['EMAIL' => ''])];
			}
			return array_map(static function ($email) use ($template) {
				return array_merge($template, ['EMAIL' => $email['email']]);
			}, $emails);
		}, $contacts));

		$this->cache->set($cacheKey, $result,
			$this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_CACHE_TTL_CONTACTS, Application::APP_CONFIG_CACHE_TTL_CONTACTS_DEFAULT)
		);

		return $result;
	}

	/**
	 * @throws ServiceException
	 */
	public function createOrUpdate($properties) {
		throw new ServiceException("Operation not available", 403);
	}

	/**
	 * @throws ServiceException
	 */
	public function getPermissions() {
		throw new ServiceException("Operation not available", 403);
	}

	/**
	 * @throws ServiceException
	 */
	public function delete($id) {
		throw new ServiceException("Operation not available", 403);
	}

	public function isShared(): bool {
		return false;
	}

	public function isSystemAddressBook(): bool {
		return true;
	}
}
