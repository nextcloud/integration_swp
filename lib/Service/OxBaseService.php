<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Swp\Service;

use OCA\Swp\AppInfo\Application;
use OCA\Swp\Exception\ServiceException;
use OCP\IAppConfig;
use Psr\Log\LoggerInterface;

class OxBaseService {

	private string $oxBaseUrl;

	public function __construct(
		private IAppConfig $appConfig,
		private TokenService $tokenService,
		private LoggerInterface $logger,
		private ?string $userId = null,
	) {
		// TODO check if everything works fine without userId during curl request creating share to email for example
		// POST /ocs/v2.php/apps/files_sharing/api/v1/shares?path=%2FREADME.md&shareType=4&shareWith=blabla%40supermail.org&can_edit=0&can_delete=0
		/*
		if ($userId === null) {
			return;
		}
		*/
		$this->oxBaseUrl = $this->appConfig->getValueString(Application::APP_ID, Application::APP_CONFIG_OX_URL);
	}

	public function checkSetup(): bool {
		return $this->userId !== null && $this->oxBaseUrl !== '';
	}

	public function getOxBaseUrl(string $endpoint): string {
		return ltrim(rtrim($this->oxBaseUrl, '/') . '/' . trim($endpoint, '/'), '/');
	}

	protected function getOxOptions(): array {
		$oxDebugUserToken = $this->appConfig->getValueString(Application::APP_ID, 'ox-usertoken');

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
