<?php
/*
 * @copyright Copyright (c) 2021 Julien Veyssier <eneiluj@posteo.net>
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
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


namespace OCA\SpsBmi\Controller;

use OCA\SpsBmi\Model\Token;
use OCA\SpsBmi\Service\MenuService;
use OCA\SpsBmi\Service\TokenService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class MenuController extends Controller {
	/**
	 * @var MenuService
	 */
	private $menuService;

	public function __construct($appName,
								IRequest $request,
								MenuService $menuService,
								?string $userId) {
		parent::__construct($appName, $request);
		$this->menuService = $menuService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getRemoteImage(string $url): DataDisplayResponse {
		$image = $this->menuService->getImage($url);
		if ($image === null) {
			return new DataDisplayResponse('', Http::STATUS_NOT_FOUND);
		} else {
			$response = new DataDisplayResponse($image['body'], Http::STATUS_OK, ['Content-Type' => $image['mimetype']]);
			$response->cacheFor(60 * 60 * 24);
			return $response;
		}
	}
}
