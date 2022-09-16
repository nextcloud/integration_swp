<?php
/**
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


namespace OCA\Phoenix\Controller;

use OCA\Phoenix\Model\Token;
use OCA\Phoenix\Service\TokenService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class PageController extends Controller {
	public function __construct($appName, IRequest $request) {
		parent::__construct($appName, $request);
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 * @UseSession
	 */
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
}
