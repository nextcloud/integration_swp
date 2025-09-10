<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Swp\Listener;

use OCA\Swp\AppInfo\Application;
use OCA\Swp\Service\TokenService;
use OCA\UserOIDC\Event\TokenObtainedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Psr\Log\LoggerInterface;

/**
 * @implements IEventListener<TokenObtainedEvent>
 */
class TokenObtainedEventListener implements IEventListener {

	public function __construct(
		private LoggerInterface $logger,
		private TokenService $tokenService,
	) {
	}

	public function handle(Event $event): void {
		$this->logger->debug('handling TokenObtainedEvent', ['app' => Application::APP_ID]);
		if (!$event instanceof TokenObtainedEvent) {
			return;
		}

		$token = $event->getToken();
		$provider = $event->getProvider();

		$tokenData = $token;
		$this->logger->debug('Storing the token: ' . json_encode($tokenData), ['app' => Application::APP_ID]);
		$this->tokenService->storeToken(array_merge($tokenData, ['provider_id' => $provider->getId()]));
	}
}
