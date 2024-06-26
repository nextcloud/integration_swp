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

namespace OCA\Swp\Service;

use OCA\Swp\AppInfo\Application;
use OCA\Swp\Exception\ServiceException;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class OxBaseService {

	private string $oxBaseUrl;

	public function __construct(
		private IConfig $config,
		private TokenService $tokenService,
		private LoggerInterface $logger,
		private ?string $userId = null
	) {
		// TODO check if everything works fine without userId during curl request creating share to email for example
		// POST /ocs/v2.php/apps/files_sharing/api/v1/shares?path=%2FREADME.md&shareType=4&shareWith=blabla%40supermail.org&can_edit=0&can_delete=0
		/*
		if ($userId === null) {
			return;
		}
		*/
		$this->oxBaseUrl = $config->getAppValue(Application::APP_ID, Application::APP_CONFIG_OX_URL);
	}

	public function checkSetup(): bool {
		return $this->userId !== null && $this->oxBaseUrl !== '';
	}

	public function getOxBaseUrl(string $endpoint): string {
		return ltrim(rtrim($this->oxBaseUrl, '/') . '/' . trim($endpoint, '/'), '/');
	}

	protected function getOxOptions(): array {
		$oxDebugUserToken = $this->config->getAppValue(Application::APP_ID, 'ox-usertoken');

		$oidcToken = $this->tokenService->getToken();
		if (!$oidcToken && $oxDebugUserToken === '') {
			$this->logger->debug('[OxBaseService::getOxOptions] could not find OX token in session or the debug one');
			throw new ServiceException('Could not get ox request options');
		}
		$oxToken = $oxDebugUserToken !== '' ? $oxDebugUserToken : $oidcToken->getAccessToken();

		return [
			'headers' => [
				'Authorization' => 'Bearer ' . $oxToken,
			],
		];
	}
}
