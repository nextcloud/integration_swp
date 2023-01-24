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
namespace OCA\Phoenix;

use OCA\Phoenix\AppInfo\Application;
use OCA\Phoenix\Exception\ServiceException;
use OCA\Phoenix\Service\OxContactsService;
use OCP\IAddressBook;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;

class OxAddressBook implements IAddressBook {

	/** @var ICache */
	private $cache;
	private IConfig $config;
	private OxContactsService $oxContactsService;
	private ?string $userId;

	public function __construct(IConfig $config,
								ICacheFactory $cacheFactory,
								OxContactsService $oxContactsService,
								?string $userId) {
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID . '_contacts');
		$this->config = $config;
		$this->oxContactsService = $oxContactsService;
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
	 *    ['id' => 0, 'FN' => 'Thomas Müller', 'EMAIL' => 'a@b.c', 'GEO' => '37.386013;-122.082932'],
	 *    ['id' => 5, 'FN' => 'Thomas Tanghus', 'EMAIL' => ['d@e.f', 'g@h.i']]
	 *  ]
	 *
	 * @param $pattern
	 * @param $searchProperties
	 * @param $options
	 * @return array|array[]|mixed
	 * @throws \JsonException
	 * @since 5.0.0
	 */
	public function search($pattern, $searchProperties, $options) {
		// use all arguments combined with the user id as a cache key
		$cacheKey = md5(json_encode([
			$this->userId, $pattern, $searchProperties, $options
		], JSON_THROW_ON_ERROR));
		$hit = $this->cache->get($cacheKey);

		$debug = $this->config->getSystemValueBool('debug', false);
		if (!$debug && $hit !== null) {
			return $hit;
		}
		try {
			$result = $this->oxContactsService->search($pattern, $options);
		} catch (ServiceException $e) {
			return [];
		}

		// format results
		if (!is_array($result)) {
			return [];
		}

		// get rid of contacts that come from users (user_id/524 field is 0)
		$filteredResult = array_filter(
			$result,
			static function ($c) {
				return is_array($c) && isset($c[6]) && $c[6] === 0;
			}
		);

		$formattedResult = array_map(
			function ($c) {
				$formattedContact = [
					//'id' => $c[0],
					'FN' => $c[2],
				];
				if ($c[3]) {
					$formattedContact['EMAIL'] = [$c[3]];
				}
				return $formattedContact;
			},
			$filteredResult
		);

		$this->cache->set($cacheKey, $formattedResult,
			$this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_CACHE_TTL_CONTACTS, Application::APP_CONFIG_CACHE_TTL_CONTACTS_DEFAULT)
		);

		return $formattedResult;
	}

	/**
	 * @throws ServiceException
	 */
	public function createOrUpdate($properties) {
		throw new ServiceException('Operation not available', 403);
	}

	/**
	 * @throws ServiceException
	 */
	public function getPermissions() {
		throw new ServiceException('Operation not available', 403);
	}

	/**
	 * @throws ServiceException
	 */
	public function delete($id) {
		throw new ServiceException('Operation not available', 403);
	}

	public function isShared(): bool {
		return false;
	}

	public function isSystemAddressBook(): bool {
		return true;
	}
}
