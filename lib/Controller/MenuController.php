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
