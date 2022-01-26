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

namespace OCA\SpsBmi\Service;

use OCA\SpsBmi\AppInfo\Application;
use OCA\SpsBmi\Model\Token;
use OCA\UserOIDC\Db\Provider;
use OCA\UserOIDC\Db\ProviderMapper;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

class MenuService {
	private const INVALIDATE_MENU_CACHE_AFTER_SECONDS = 3600;

	/** @var ISession */
	private $session;
	/** @var IClient */
	private $client;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IUserSession */
	private $userSession;
	/** @var IRequest */
	private $request;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var ICache
	 */
	private $cache;

	public function __construct(ISession $session,
								IClientService $client,
								IURLGenerator $urlGenerator,
								IUserSession $userSession,
								LoggerInterface $logger,
								IRequest $request,
								ICacheFactory $cacheFactory) {
		$this->session = $session;
		$this->client = $client->newClient();
		$this->urlGenerator = $urlGenerator;
		$this->userSession = $userSession;
		$this->request = $request;
		$this->logger = $logger;
		$this->cache = $cacheFactory->createDistributed(Application::APP_ID);
	}

	public function getMenuJson(Token $token): ?string {
		// make the menu request (and cache it)
		/*
		$providerId = $token->getProviderId();
		$cacheKey = 'menuitems-' . $providerId;
		$cachedMenu = $this->cache->get($cacheKey);
		if ($cachedMenu === null) {
			// TODO set menu URL
			$url = '???';
			$options = [
				'headers' => [
					'Authorization'  => 'Bearer ' . $token->getIdToken(),
				],
			];

			$response = $this->client->get($url, $options);
			$cachedMenu = $response->getBody();
			$this->cache->set($cacheKey, $cachedMenu, self::INVALIDATE_MENU_CACHE_AFTER_SECONDS);
		}

		return $cachedMenu;
		*/
		return '
{
  "categories": [
    {
      "identifier": "technical_groupname1",
      "display_name": "First cat",
      "entries": [
        {
          "identifier": "cat1_item1",
          "icon_url": "https://www.downloadclipart.net/svg/31379-logo-vector.svg",
          "display_name": "First item",
          "link": "https://duckduckgo.com/one",
          "description": "1-1",
          "keywords": "kw1 kw2"
        },
        {
          "identifier": "cat1_item2",
          "icon_url": "https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg",
          "display_name": "Second item",
          "link": "https://duckduckgo.com/two",
          "description": "1-2",
          "keywords": "kw3 kw4"
        }
      ]
    },
    {
      "identifier": "technical_groupname2",
      "display_name": "Second cat",
      "entries": [
        {
          "identifier": "cat2_item1",
          "icon_url": "https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg",
          "display_name": "First item",
          "link": "https://duckduckgo.com/three",
          "description": "2-1",
          "keywords": "kw1 kw2"
        },
        {
          "identifier": "cat2_item2",
          "icon_url": "https://www.downloadclipart.net/svg/31379-logo-vector.svg",
          "display_name": "Second item",
          "link": "https://duckduckgo.com/four",
          "description": "2-2",
          "keywords": "kw3 kw4"
        }
      ]
    }
  ]
}
				';
	}

	public function getImage(string $url): ?array {
		try {
			$response = $this->client->get($url);
			$body = $response->getBody();
			$mimetype = $response->getHeader('Content-Type');
			return [
				'body' => $body,
				'mimetype' => $mimetype,
			];
		} catch (\Exception $e) {
			$this->logger->error('Failed to get image', ['exception' => $e]);
			return null;
		}
	}
}
