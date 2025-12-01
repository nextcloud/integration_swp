<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Swp\Controller;

use Exception;
use OC\User\NoUserException;
use OCA\Swp\AppInfo\Application;
use OCA\Swp\Model\Token;
use OCA\Swp\Service\TokenService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;
use Throwable;

class PageController extends Controller {

	public function __construct(
		$appName,
		IRequest $request,
		private IURLGenerator $urlGenerator,
		private IRootFolder $rootFolder,
		private IAppConfig $appConfig,
		private IClientService $clientService,
		private LoggerInterface $logger,
		private IL10N $l10n,
		private ?string $userId,
	) {
		parent::__construct($appName, $request);
	}

	#[PublicPage]
	#[NoCSRFRequired]
	#[UseSession]
	public function index() {
		/** @var Token $token */
		$token = \OC::$server->get(TokenService::class)->getToken(true);
		if ($token === null) {
			return new JSONResponse([]);
		}
		return new JSONResponse([
			'token' => $token,
			'expires_in_seconds' => ($token->getCreatedAt() + $token->getExpiresIn()) - time()
		]);
	}

	#[PublicPage]
	#[NoCSRFRequired]
	public function getLogo(): DataDisplayResponse {
		$logoImageUrl = $this->appConfig->getValueString(Application::APP_ID, Application::APP_CONFIG_LOGO_IMAGE_URL);
		if ($logoImageUrl) {
			$client = $this->clientService->newClient();
			try {
				$logoResponse = $client->get($logoImageUrl);
				$fileContent = $logoResponse->getBody();
				$mimeType = $logoResponse->getHeader('Content-Type');
				if (is_array($mimeType) && count($mimeType) > 0) {
					$mimeType = $mimeType[0];
				}
				$response = new DataDisplayResponse($fileContent, Http::STATUS_OK, ['Content-Type' => $mimeType]);
				$response->cacheFor(60 * 60);
				return $response;
			} catch (Exception|Throwable $e) {
				$this->logger->error('Failed to get logo at ' . $logoImageUrl, ['exception' => $e]);
			}
		}

		// fallback to local logo
		$fileContent = file_get_contents(__DIR__ . '/../../img/phoenix_suite_logo-Assets/SVG/phoenix_suite_logo.svg');
		$response = new DataDisplayResponse($fileContent, Http::STATUS_OK, ['Content-Type' => 'image/svg+xml']);
		$response->cacheFor(60 * 60);
		return $response;
	}

	/**
	 * @param string $ext
	 * @param string|null $directory
	 * @param string|null $name
	 * @return DataResponse|RedirectResponse
	 * @throws InvalidPathException
	 * @throws NoUserException
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	#[NoadminRequired]
	#[NoCSRFRequired]
	public function createDocument(string $ext, ?string $directory = null, ?string $name = null) {
		if (!in_array($ext, ['docx', 'xlsx', 'pptx', 'odt', 'ods', 'odp', 'odg', 'txt', 'md'])) {
			return new DataResponse('Unsupported format', Http::STATUS_BAD_REQUEST);
		}

		$userFolder = $this->rootFolder->getUserFolder($this->userId);

		// optionally choose target directory
		if ($directory !== null && $userFolder->nodeExists($directory)) {
			$targetDir = $userFolder->get($directory);
			if (!($targetDir instanceof Folder)) {
				return new DataResponse('Target directory does not exist', Http::STATUS_BAD_REQUEST);
			}
		} else {
			$targetDir = $userFolder;
		}

		// optionally choose file name
		if ($name !== null) {
			$newFileName = $name . '.' . $ext;
		} else {
			$newFileName = $this->l10n->t('New document') . '.' . $ext;
		}

		$uniqueNewFileName = $newFileName;
		$counter = 1;
		while ($targetDir->nodeExists($uniqueNewFileName)) {
			$uniqueNewFileName = preg_replace('/\.' . $ext . '$/', ' (' . strval($counter) . ').' . $ext, $newFileName);
			$counter++;
		}

		$newFile = $targetDir->newFile($uniqueNewFileName);

		$finalUrl = $this->urlGenerator->getAbsoluteURL(
			$this->urlGenerator->linkToRoute('files.View.showFile', ['fileid' => $newFile->getId()])
		);
		return new RedirectResponse($finalUrl);
	}
}
