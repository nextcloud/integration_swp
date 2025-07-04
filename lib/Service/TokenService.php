<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Swp\Service;

use OCA\Swp\AppInfo\Application;
use OCA\Swp\Model\Token;
use OCA\Swp\Vendor\Firebase\JWT\JWT;
use OCA\Swp\Vendor\Firebase\JWT\Key;
use OCA\UserOIDC\Db\Provider;
use OCA\UserOIDC\Db\ProviderMapper;
use OCA\UserOIDC\Service\DiscoveryService;
use OCP\App\IAppManager;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Security\ICrypto;
use Psr\Log\LoggerInterface;

class TokenService {

	private const INVALIDATE_DISCOVERY_CACHE_AFTER_SECONDS = 3600;
	private const SESSION_TOKEN_KEY = Application::APP_ID . '-user-token';

	private IClient $client;
	private ICache $cache;

	public function __construct(
		ICacheFactory $cacheFactory,
		IClientService $clientService,
		private ISession $session,
		private IURLGenerator $urlGenerator,
		private IUserSession $userSession,
		private LoggerInterface $logger,
		private IRequest $request,
		private IConfig $config,
		private ICrypto $crypto,
		private IAppManager $appManager,
	) {
		$this->client = $clientService->newClient();
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID);
	}

	public function storeToken(array $tokenData): Token {
		$token = new Token($tokenData);
		$this->session->set(self::SESSION_TOKEN_KEY, json_encode($token, JSON_THROW_ON_ERROR));
		$this->logger->info('Store token', ['app' => Application::APP_ID]);
		return $token;
	}

	public function getToken(bool $refresh = true): ?Token {
		$sessionData = $this->session->get(self::SESSION_TOKEN_KEY);
		if (!$sessionData) {
			return null;
		}

		$token = new Token(json_decode($sessionData, true, 512, JSON_THROW_ON_ERROR));
		if ($token->isExpired()) {
			return $token;
		}

		if ($refresh && $token->isExpiring()) {
			$token = $this->refresh($token);
		}
		return $token;
	}

	public function refresh(Token $token) {
		/** @var ProviderMapper $providerMapper */
		$providerMapper = \OC::$server->get(ProviderMapper::class);
		$oidcProvider = $providerMapper->getProvider($token->getProviderId());
		$discovery = $this->obtainDiscovery($oidcProvider);

		try {
			$clientSecret = $oidcProvider->getClientSecret();
			$userOidcVersion = $this->appManager->getAppVersion('user_oidc');
			// oidc provider secret encryption was introduced in v1.3.3
			if (version_compare($userOidcVersion, '1.3.3', '>=')) {
				// attempt to decrypt the oidc provider secret
				try {
					$clientSecret = $this->crypto->decrypt($oidcProvider->getClientSecret());
				} catch (\Exception $e) {
					$this->logger->error('Failed to decrypt oidc client secret', ['app' => Application::APP_ID]);
				}
			}
			$this->logger->debug('Refreshing the token: ' . $discovery['token_endpoint'], ['app' => Application::APP_ID]);
			$result = $this->client->post(
				$discovery['token_endpoint'],
				[
					'body' => [
						'client_id' => $oidcProvider->getClientId(),
						'client_secret' => $clientSecret,
						'grant_type' => 'refresh_token',
						'refresh_token' => $token->getRefreshToken(),
						// TODO check if we need a different scope for this
						//'scope' => $oidcProvider->getScope(),
					],
				]
			);
			$this->logger->debug('PARAMS: ' . json_encode([
				'client_id' => $oidcProvider->getClientId(),
				'client_secret' => $clientSecret,
				'grant_type' => 'refresh_token',
				'refresh_token' => $token->getRefreshToken(),
				// TODO check if we need a different scope for this
				//'scope' => $oidcProvider->getScope(),
			]), ['app' => Application::APP_ID]);
			$body = $result->getBody();
			$bodyArray = json_decode(trim($body), true, 512, JSON_THROW_ON_ERROR);
			$this->logger->debug('Refresh token success: "' . trim($body) . '"', ['app' => Application::APP_ID]);
			return $this->storeToken(
				array_merge(
					$bodyArray,
					['provider_id' => $token->getProviderId()],
				)
			);
		} catch (\Exception $e) {
			$this->logger->error('Failed to refresh token ', ['exception' => $e, 'app' => Application::APP_ID]);
			// Failed to refresh, return old token which will be retried or otherwise timeout if expired
			return $token;
		}
	}

	public function obtainDiscovery(Provider $provider): array {
		$cacheKey = 'discovery-' . $provider->getId();
		$cachedDiscovery = $this->cache->get($cacheKey);
		$debug = $this->config->getSystemValueBool('debug', false);
		if ($debug || $cachedDiscovery === null) {
			$url = $provider->getDiscoveryEndpoint();
			$this->logger->debug('Obtaining discovery endpoint: ' . $url, ['app' => Application::APP_ID]);

			$response = $this->client->get($url);
			$cachedDiscovery = $response->getBody();
			$this->cache->set($cacheKey, $cachedDiscovery, self::INVALIDATE_DISCOVERY_CACHE_AFTER_SECONDS);
		}

		return json_decode($cachedDiscovery, true, 512, JSON_THROW_ON_ERROR);
	}

	public function decodeIdToken(Token $token): array {
		/** @var ProviderMapper $providerMapper */
		$providerMapper = \OC::$server->get(ProviderMapper::class);
		/** @var DiscoveryService $discoveryService */
		$discoveryService = \OC::$server->get(DiscoveryService::class);
		$oidcProvider = $providerMapper->getProvider($token->getProviderId());

		// converting \OCA\UserOIDC\Vendor\Firebase\JWT\Key[] to \OCA\Swp\Vendor\Firebase\JWT\Key[]
		// because OCA\Swp\Vendor\Firebase\JWT\JWT::decode checks the types
		// this issue can also be solved by just importing OCA\UserOIDC\Vendor\Firebase\JWT\JWT instead of OCA\Swp\Vendor\Firebase\JWT\JWT
		/** @var \OCA\UserOIDC\Vendor\Firebase\JWT\Key[] $jwks */
		$jwks = $discoveryService->obtainJWK($oidcProvider, $token->getIdToken());
		$myJwks = [];
		foreach ($jwks as $kid => $jwk) {
			$material = $jwk->getKeyMaterial();
			$alg = $jwk->getAlgorithm();
			$myJwks[$kid] = new Key($material, $alg);
		}
		JWT::$leeway = 60;
		$idTokenObject = JWT::decode($token->getIdToken(), $myJwks);
		return json_decode(json_encode($idTokenObject), true);
	}

	public function reauthenticate() {
		$token = $this->getToken(false);
		if ($token === null) {
			return;
		}

		// Logout the user and redirect to the oidc login flow to gather a fresh token
		$this->userSession->logout();
		$redirectUrl = $this->urlGenerator->getAbsoluteURL('/index.php/apps/user_oidc/login/' . strval($token->getProviderId())) .
			'?redirectUrl=' . urlencode($this->request->getRequestUri());
		header('Location: ' . $redirectUrl);
		exit();
	}
}
