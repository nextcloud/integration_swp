<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Swp;

use OCA\Swp\AppInfo\Application;
use OCA\Swp\Exception\ServiceException;
use OCA\Swp\Service\OxContactsService;
use OCP\IAddressBook;
use OCP\IAppConfig;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;

class OxAddressBook implements IAddressBook {

	private ICache $cache;

	public function __construct(
		ICacheFactory $cacheFactory,
		private IConfig $config,
		private IAppConfig $appConfig,
		private OxContactsService $oxContactsService,
		private ?string $userId,
	) {
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID . '_contacts');
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
	 * @return array
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

		$cacheTtl = $this->appConfig->getValueString(
			Application::APP_ID,
			Application::APP_CONFIG_CACHE_TTL_CONTACTS,
			(string)Application::APP_CONFIG_CACHE_TTL_CONTACTS_DEFAULT
		) ?: Application::APP_CONFIG_CACHE_TTL_CONTACTS_DEFAULT;
		$this->cache->set($cacheKey, $formattedResult, (int)$cacheTtl);

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
