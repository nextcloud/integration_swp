<?php
/**
 * @copyright Copyright (c) 2021 Julien Veyssier <eneiluj@posteo.net>
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
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

namespace OCA\Phoenix\Service;

use Exception;
use OCA\Phoenix\AppInfo\Application;
use OCA\Phoenix\Model\Token;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use Psr\Log\LoggerInterface;
use Throwable;

class MenuService {

	/** @var IClient */
	private $client;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var ICache
	 */
	private $cache;
	/**
	 * @var IFactory
	 */
	private $l10nFactory;
	/**
	 * @var IUserSession
	 */
	private $userSession;
	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var TokenService
	 */
	private $tokenService;
	/**
	 * @var array
	 */
	private $fallbackMenuEntries;

	public function __construct(IClientService $client,
								IUserSession $userSession,
								LoggerInterface $logger,
								IFactory $l10nFactory,
								IConfig $config,
								TokenService $tokenService,
								ICacheFactory $cacheFactory) {
		$this->client = $client->newClient();
		$this->userSession = $userSession;
		$this->logger = $logger;
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID);
		$this->l10nFactory = $l10nFactory;
		$this->config = $config;
		$this->tokenService = $tokenService;
		$this->fallbackMenuEntries = [
			'categories' => [
				[
					'identifier' => 'technical_groupname1',
					'display_name' => 'Collaboration',
					'entries' => [
						[
							'identifier' => 'cat1_item1',
							'icon_url' => 'https://www.downloadclipart.net/svg/31379-logo-vector.svg',
							'display_name' => 'Files',
							'link' => 'https://duckduckgo.com/one',
							'description' => '1-1',
							'keywords' => 'kw1 kw2'
						],
						[
							'identifier' => 'cat1_item2',
							'icon_url' => 'https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg',
							'display_name' => 'Chat',
							'link' => 'https://duckduckgo.com/two',
							'description' => '1-2',
							'keywords' => 'kw3 kw4'
						]
					]
				],
				[
					'identifier' => 'technical_groupname2',
					'display_name' => 'Groupware',
					'entries' => [
						[
							'identifier' => 'cat2_item1',
							'icon_url' => 'https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg',
							'display_name' => 'Mail is a very long item which should be displayed on multiple lines',
							'link' => 'https://duckduckgo.com/three',
							'description' => '2-1',
							'keywords' => 'kw1 kw2'
						],
						[
							'identifier' => 'cat2_item2',
							'icon_url' => 'https://www.downloadclipart.net/svg/31379-logo-vector.svg',
							'display_name' => 'Calendar',
							'link' => 'https://duckduckgo.com/four',
							'description' => '2-2',
							'keywords' => 'kw3 kw4',
						]
					]
				]
			]
		];
	}

	public function getMenuJson(Token $token): ?array {
		try {
			$jsonMenuUrl = $this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_NAVIGATION_URL, '');
			if ($jsonMenuUrl !== '') {
				// make the menu request (and cache it)
				$providerId = $token->getProviderId();
				$cacheKey = 'menuitems-' . $providerId;
				$cachedMenu = $this->cache->get($cacheKey);
				if ($cachedMenu === null) {
					$lang = $this->l10nFactory->getUserLanguage($this->userSession->getUser());
					$lang = preg_replace('/^de$/', 'de-DE', $lang);
					$lang = preg_replace('/^fr$/', 'fr-FR', $lang);
					$params = [
						'language' => $lang,
					];
					$jsonMenuUrl .= '?' . http_build_query($params);
					$options = [
						'headers' => [],
					];

					// get headers from config
					$sharedSecret = $this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_NAVIGATION_SHARED_SECRET, '');
					if ($sharedSecret !== '') {
						$usernameTokenAttribute = $this->config->getAppValue(
							Application::APP_ID, Application::APP_CONFIG_NAVIGATION_USERNAME_ATTRIBUTE, ''
						) ?: 'preferred_username';
						// get the preferred_username token attribute
						$decodedToken = $this->tokenService->decodeIdToken($token);
						$username = $decodedToken[$usernameTokenAttribute] ?? '';

						$authType = $this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_NAVIGATION_AUTH_TYPE, 'basic') ?: 'basic';
						$useBasicAuth = $authType === 'basic';
						if ($useBasicAuth) {
							$options['headers']['Authorization'] = 'Basic ' . base64_encode($username . ':' . $sharedSecret);
						} else {
							$options['headers']['Authorization'] = 'Bearer ' . $sharedSecret;
							$options['headers']['X-Ucs-Username'] = $username;
						}
						$this->logger->info('Navigation json request: shared secret: "' . $sharedSecret . '"');
						$this->logger->info('UCSusername "' . $username . '"');
					}

					$response = $this->client->get($jsonMenuUrl, $options);
					$cachedMenu = $response->getBody();
					$cacheDuration = $this->config->getAppValue(
						Application::APP_ID,
						Application::APP_CONFIG_CACHE_NAVIGATION_JSON,
						Application::APP_CONFIG_CACHE_NAVIGATION_JSON_DEFAULT
					);
					$this->cache->set($cacheKey, $cachedMenu, $cacheDuration);
				}

				return json_decode($cachedMenu, true);
			}
		} catch (Exception | Throwable $e) {
			$this->logger->error('Error while fetching navigation json content', ['exception' => $e]);
		}

		// backup dummy menu value
		return $this->fallbackMenuEntries;
	}

	/**
	 * @param string $itemId
	 * @return array|null
	 * @throws \JsonException
	 */
	public function getMenuEntryIcon(string $itemId): ?array {
		$token = $this->tokenService->getToken();
		$menuJson = $this->getMenuJson($token);
		if ($menuJson === null) {
			return null;
		}
		$url = $this->findItemIconUrl($menuJson, $itemId);
		if ($url === null) {
			return null;
		}
		try {
			$response = $this->client->get($url);
			$body = $response->getBody();
			$mimetype = $response->getHeader('Content-Type');
			return [
				'body' => $body,
				'mimetype' => $mimetype,
			];
		} catch (Exception $e) {
			$this->logger->error('Failed to get image', ['exception' => $e]);
			return null;
		}
	}

	/**
	 * @param array $menu
	 * @param string $itemId
	 * @return string|null
	 */
	private function findItemIconUrl(array $menu, string $itemId): ?string {
		foreach ($menu['categories'] as $category) {
			foreach ($category['entries'] as $entry) {
				if (($entry['identifier'] ?? '') === $itemId) {
					return $entry['icon_url'];
				}
			}
		}
		return null;
	}
}
