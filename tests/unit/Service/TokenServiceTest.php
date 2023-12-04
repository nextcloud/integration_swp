<?php

namespace OCA\Swp\Tests;


use OCA\Swp\AppInfo\Application;
use OCA\Swp\Model\Token;
use OCA\Swp\Service\TokenService;
use OCA\Swp\Service\UserOidcService;
use OCA\UserOIDC\Db\Provider;
use OCA\UserOIDC\Db\ProviderMapper;
use OCA\UserOIDC\Service\DiscoveryService;
use OCA\UserOIDC\Vendor\Firebase\JWT\JWK;
use OCP\App\IAppManager;
use OCP\Http\Client\IClientService;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Security\ICrypto;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class TokenServiceTest extends TestCase {

	public function setUp(): void {
		parent::setUp();

//		$this->clientService = $this->createMock(ClientService::class);

		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->clientService = $this->createMock(IClientService::class);
		$this->session = $this->createMock(ISession::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->request = $this->createMock(IRequest::class);
		$this->config = $this->createMock(IConfig::class);
		$this->crypto = $this->createMock(ICrypto::class);
		$this->appManager = $this->createMock(IAppManager::class);
		$this->userOidcService = $this->createMock(UserOidcService::class);

		$this->discoveryService = $this->createMock(DiscoveryService::class);
		$this->providerMapper = $this->createMock(ProviderMapper::class);

		$this->service = new TokenService(
			$this->cacheFactory,
			$this->clientService,
			$this->session,
			$this->urlGenerator,
			$this->userSession,
			$this->logger,
			$this->request,
			$this->config,
			$this->crypto,
			$this->appManager,
			$this->userOidcService,
		);
	}

	public function testDummy() {
		$app = new Application();
		$this->assertEquals('integration_swp', $app::APP_ID);
	}

	public function testDecodeIdToken() {
		$tokenDataString = file_get_contents(__DIR__ . '/../../data/token_data.json');
		$tokenData = json_decode($tokenDataString, true);
		$token = new Token($tokenData);
		$this->assertEquals(3, $token->getProviderId());

		$this->userOidcService->method('getUserOidcProviderMapper')
			->willReturn($this->providerMapper);
		$this->userOidcService->method('getUserOidcDiscoveryService')
			->willReturn($this->discoveryService);

		$provider = new Provider();
		$this->providerMapper->method('getProvider')
			->willReturn($provider);

		$jwksUriResponse = file_get_contents(__DIR__ . '/../../data/jwks_uri_response.json');
		$jwksRawArray = json_decode($jwksUriResponse, true);
		$jwks = JWK::parseKeySet($jwksRawArray, 'RS256');
		$this->discoveryService->method('obtainJWK')
			->willReturn($jwks);

		$decodedToken = $this->service->decodeIdToken($token);
		// get expected ID token payload
		$expectedIdTokenPayload = file_get_contents(__DIR__ . '/../../data/decoded_id_token.json');
		$expectedIdToken = json_decode($expectedIdTokenPayload, true);

		$this->assertEquals($expectedIdToken, $decodedToken);
	}
}
