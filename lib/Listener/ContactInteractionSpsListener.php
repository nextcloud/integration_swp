<?php

declare(strict_types=1);

/**
 * @copyright 2021 Julien Veyssier <eneiluj@posteo.net>
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
namespace OCA\Phoenix\Listener;

use OCA\Phoenix\AppInfo\Application;
use OCA\Phoenix\Exception\ServiceException;
use OCA\Phoenix\OxAddressBook;
use OCA\Phoenix\Service\OxContactsService;
use OCP\Contacts\Events\ContactInteractedWithEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\ICacheFactory;
use Psr\Log\LoggerInterface;

class ContactInteractionSpsListener implements IEventListener {

	/**
	 * @var OxContactsService
	 */
	private $contactsService;
	/**
	 * @var OxAddressBook
	 */
	private $oxAddressBook;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var ICacheFactory
	 */
	private $cacheFactory;
	/**
	 * @var string|null
	 */
	private $userId;

	public function __construct(OxContactsService $contactsService,
								OxAddressBook $oxAddressBook,
								ICacheFactory $cacheFactory,
								LoggerInterface $logger,
								?string $userId) {
		$this->contactsService = $contactsService;
		$this->oxAddressBook = $oxAddressBook;
		$this->logger = $logger;
		$this->cacheFactory = $cacheFactory;
		$this->userId = $userId;
	}

	public function handle(Event $event): void {
		if (!($event instanceof ContactInteractedWithEvent)) {
			return;
		}
		if ($event->getEmail() !== null) {
			try {
				$email = $event->getEmail();
				$cache = $this->cacheFactory->createDistributed(Application::APP_ID . '_contacts');

				// make sure we don't get outdated cached search results
				$cacheKey = md5(json_encode([
					$this->userId, $email, [], []
				], JSON_THROW_ON_ERROR));
				$cache->remove($cacheKey);

				// if the contact already exists in the OX addr book, we don't create it again
				$searchResults = $this->oxAddressBook->search($email, [], []);
				foreach ($searchResults as $contact) {
					$cEmail = $contact['EMAIL'][0] ?? null;
					if ($cEmail === $email) {
						return;
					}
				}

				$this->contactsService->createContact($email, $email);
				// clear the entire cache (because we can't know exactly which cache keys to clear)
				$cache->clear();
			} catch (ServiceException $e) {
				$this->logger->debug('Recent contact creation in OX contact failed', [
					'app' => Application::APP_ID,
					'exception' => $e,
				]);
			}
		}
	}
}
