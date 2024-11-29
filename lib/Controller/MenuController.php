<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);


namespace OCA\Swp\Controller;

use OCA\Swp\Service\MenuService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\IRequest;

class MenuController extends Controller {

	public function __construct(
		$appName,
		IRequest $request,
		private MenuService $menuService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getMenuEntryIcon(string $itemId): DataDisplayResponse {
		$icon = $this->menuService->getMenuEntryIcon($itemId);
		if ($icon === null) {
			return new DataDisplayResponse('', Http::STATUS_NOT_FOUND);
		} else {
			$response = new DataDisplayResponse(
				$icon['body'],
				Http::STATUS_OK,
				['Content-Type' => $icon['mimetype']]
			);
			$response->cacheFor(60 * 60 * 24);
			return $response;
		}
	}
}
