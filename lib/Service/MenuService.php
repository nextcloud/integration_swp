<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Swp\Service;

use Exception;
use OCA\Swp\AppInfo\Application;
use OCA\Swp\Model\Token;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use Psr\Log\LoggerInterface;
use Throwable;

class MenuService {

	private IClient $client;
	private ICache $cache;

	public function __construct(
		IClientService $clientService,
		ICacheFactory $cacheFactory,
		private IUserSession $userSession,
		private LoggerInterface $logger,
		private IFactory $l10nFactory,
		private IConfig $config,
		private TokenService $tokenService,
		private IURLGenerator $urlGenerator,
	) {
		$this->client = $clientService->newClient();
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID);
	}

	public function getMenuJson(?Token $token): ?array {
		if ($token === null) {
			return $this->getFromFile('fake.menu.example.json');
		}
		try {
			$jsonMenuUrl = $this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_NAVIGATION_URL, '');
			if ($jsonMenuUrl !== '') {
				// make the menu request (and cache it)
				$providerId = $token->getProviderId();
				$cacheKey = 'menuitems-' . strval($providerId);
				$cachedMenu = $this->cache->get($cacheKey);
				$debug = $this->config->getSystemValueBool('debug', false);
				if ($debug || $cachedMenu === null) {
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
						(string)Application::APP_CONFIG_CACHE_NAVIGATION_JSON_DEFAULT
					) ?: Application::APP_CONFIG_CACHE_NAVIGATION_JSON_DEFAULT;
					$this->cache->set($cacheKey, $cachedMenu, (int)$cacheDuration);
				}

				return json_decode($cachedMenu, true);
			}
		} catch (Exception|Throwable $e) {
			$this->logger->error('Error while fetching navigation json content', ['exception' => $e]);
		}

		// backup dummy menu value
		$dummyValue = $this->getFromFile('fake.menu.example.json');
		$dummyValue['categories'][0]['entries'][0]['link'] = $this->urlGenerator->linkToRouteAbsolute('files.view.index');
		return $dummyValue;
	}

	/**
	 * @param string $fileName
	 * @return array|null
	 */
	private function getFromFile(string $fileName): ?array {
		$filePath = __DIR__ . '/' . $fileName;
		if (file_exists($filePath)) {
			return json_decode(file_get_contents($filePath), true);
		}
		return null;
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
