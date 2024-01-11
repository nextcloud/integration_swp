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

namespace OCA\Swp\Listener;

use OCA\Swp\AppInfo\Application;
//use OCA\Swp\Service\OxMailService;
use OCA\Swp\Service\TokenService;
use OCA\UserOIDC\Event\TokenObtainedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
//use OCP\Http\Client\IClientService;
use Psr\Log\LoggerInterface;

/**
 * @implements IEventListener<Event>
 */
class TokenObtainedEventListener implements IEventListener {

	public function __construct(
		// private IClientService $clientService,
		// private OxMailService $mailService,
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
		$discovery = $event->getDiscovery();

		//$refreshToken = $token['refresh_token'] ?? null;

		//if (!$refreshToken) {
		//	$this->logger->debug('handle TokenObtainedEvent NO REFRESH TOKEN', ['app' => Application::APP_ID]);
		//	return;
		//}

		//$client = $this->clientService->newClient();
		//$this->logger->debug('TokenObtainedEventListener TOKEN REQUEST to ' . $discovery['token_endpoint'] . ' with refresh token=' . $refreshToken . ' and client id=' . $provider->getClientId(), ['app' => Application::APP_ID]);
		//$result = $client->post(
		//	$discovery['token_endpoint'],
		//	[
		//		'body' => [
		//			'client_id' => $provider->getClientId(),
		//			'client_secret' => $provider->getClientSecret(),
		//			'grant_type' => 'refresh_token',
		//			'refresh_token' => $refreshToken,
		//			// TODO check if we need a different scope for this
		//			'scope' => $provider->getScope(),
		//		],
		//	]
		//);
		//$this->logger->debug('refresh request STATUS CODE:' . $result->getStatusCode(), ['app' => Application::APP_ID]);

		//$tokenData = json_decode($result->getBody(), true);

		$tokenData = $token;
		$this->logger->debug('Storing the token: ' . json_encode($tokenData), ['app' => Application::APP_ID]);
		$this->tokenService->storeToken(array_merge($tokenData, ['provider_id' => $provider->getId()]));

		//		$this->mailService->resetCache();
		//		$this->mailService->fetchUnreadCounter();
	}
}
