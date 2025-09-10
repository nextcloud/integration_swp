<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);


namespace OCA\Swp\Service;

use Exception;
use OCA\Swp\AppInfo\Application;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class OxMailService extends OxBaseService {

	private ICache $cache;

	public function __construct(
		ICacheFactory $cacheFactory,
		TokenService $tokenService,
		private IAppConfig $appConfig,
		private IConfig $config,
		private IAppConfig $appConfig,
		private IClientService $clientService,
		private LoggerInterface $logger,
		private ?string $userId = null,
	) {
		parent::__construct($appConfig, $tokenService, $logger, $userId);
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID . '_unread');
	}

	public function fetchUnreadCounter(): void {
		if (!$this->checkSetup()) {
			return;
		}

		$cachedUnreadCount = $this->cache->get($this->userId);
		if ($cachedUnreadCount !== null) {
			return;
		}

		$unreadUrl = $this->getOxBaseUrl('/rest/messaging/v1/emails/inbox/unread/count');
		try {
			$client = $this->clientService->newClient();
			$response = $client->get($unreadUrl, array_merge($this->getOxOptions(), [ 'timeout' => 5 ]));
			$responseBody = $response->getBody();
			$result = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
			if (!isset($result['count'])) {
				throw new Exception('Response did not contain the unread email count: ' . $responseBody);
			}
			$this->setUnreadCounter((int)$result['count']);
		} catch (Exception $e) {
			$this->logger->error('Failed to fetch unread email counter for user ' . $this->userId, ['exception' => $e]);
			$this->setUnreadCounter(0);
		}
	}

	public function getUnreadCounter(): int {
		if (!$this->checkSetup()) {
			return 0;
		}
		$this->fetchUnreadCounter();
		return (int)$this->config->getUserValue($this->userId, Application::APP_ID, Application::USER_CONFIG_KEY_UNREAD_COUNT, '0');
	}

	public function setUnreadCounter(int $counter): void {
		$cacheTtl = $this->appConfig->getValueString(
			Application::APP_ID,
			Application::APP_CONFIG_CACHE_TTL_MAIL,
			(string)Application::APP_CONFIG_CACHE_TTL_MAIL_DEFAULT
		) ?: Application::APP_CONFIG_CACHE_TTL_MAIL_DEFAULT;
		$this->cache->set($this->userId, $counter, (int)$cacheTtl);

		$this->config->setUserValue($this->userId, Application::APP_ID, Application::USER_CONFIG_KEY_UNREAD_COUNT, (string)$counter);
	}

	public function resetCache(): void {
		$this->cache->remove($this->userId);
	}
}
