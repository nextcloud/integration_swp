<?php
/*
 * @copyright Copyright (c) 2021 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
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

namespace OCA\SpsBmi\Service;

use OCA\SpsBmi\AppInfo\Application;
use OCA\SpsBmi\Exception\ServiceException;
use OCP\IConfig;

class OxBaseService {

	/** @var IConfig */
	private $config;
	/** @var TokenService */
	private $tokenService;
	/** @var string|null */
	private $userId;

	private $oxBaseUrl;
	private $oxAppId;
	private $oxAppSecret;

	public function __construct(IConfig $config, TokenService $tokenService, $userId) {
		$this->config = $config;
		$this->tokenService = $tokenService;
		$this->userId = $userId;
		if ($userId === null) {
			return;
		}

		$this->oxBaseUrl = $this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_OX_URL);
		$this->oxAppId = $this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_OX_APPID);
		$this->oxAppSecret = $this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_OX_APPSECRET);
	}

	public function checkSetup(): bool {
		return $this->userId !== null && $this->oxBaseUrl !== '' && $this->oxAppId !== '' && $this->oxAppSecret !== '';
	}

	public function getOxBaseUrl(string $endpoint): string {
		return ltrim(rtrim($this->oxBaseUrl, '/') . '/' . trim($endpoint, '/'), '/');
	}

	protected function getOxOptions(): array {
		$oxDebugUserToken = $this->config->getAppValue(Application::APP_ID, 'ox-usertoken');

		$oidcToken = $this->tokenService->getToken();
		if (!$oidcToken && $oxDebugUserToken === '') {
			$this->logger->debug('Attempt to fetch unread count but could not find OX token');
			throw new ServiceException('Could not get ox request options');
		}
		$oxToken = $oxDebugUserToken !== '' ? $oxDebugUserToken : $oidcToken->getAccessToken();

		return [
			'headers' => [
				'X-UserToken' => $oxToken,
			],
			'auth' => [ $this->oxAppId, $this->oxAppSecret ],
		];
	}
}
