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


namespace OCA\Swp\Service;

use Exception;
use OCA\Swp\AppInfo\Application;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class OxMailService extends OxBaseService {

	private ICache $cache;

	public function __construct(
		ICacheFactory $cacheFactory,
		TokenService $tokenService,
		private IConfig $config,
		private IClientService $clientService,
		private LoggerInterface $logger,
		private ?string $userId = null
	) {
		parent::__construct($config, $tokenService, $logger, $userId);
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
		$cacheTtl = $this->config->getAppValue(
			Application::APP_ID,
			Application::APP_CONFIG_CACHE_TTL_MAIL,
			(string) Application::APP_CONFIG_CACHE_TTL_MAIL_DEFAULT
		) ?: Application::APP_CONFIG_CACHE_TTL_MAIL_DEFAULT;
		$this->cache->set($this->userId, $counter, (int) $cacheTtl);

		$this->config->setUserValue($this->userId, Application::APP_ID, Application::USER_CONFIG_KEY_UNREAD_COUNT, (string)$counter);
	}

	public function resetCache(): void {
		$this->cache->remove($this->userId);
	}
}
