<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Swp\Listener;

use OCA\Swp\AppInfo\Application;
use OCA\Swp\Exception\ServiceException;
use OCA\Swp\OxAddressBook;
use OCA\Swp\Service\OxContactsService;
use OCP\Contacts\Events\ContactInteractedWithEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\ICacheFactory;
use Psr\Log\LoggerInterface;

/**
 * @implements IEventListener<Event>
 */
class ContactInteractionSpsListener implements IEventListener {

	public function __construct(
		private OxContactsService $contactsService,
		private OxAddressBook $oxAddressBook,
		private ICacheFactory $cacheFactory,
		private LoggerInterface $logger,
		private ?string $userId,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof ContactInteractedWithEvent)) {
			return;
		}
		if ($event->getEmail() !== null) {
			try {
				$email = $event->getEmail();
				$actor = $event->getActor();
				$actorId = $actor->getUID();
				$this->logger->debug('ContactInteractionSpsListener EMAIL:' . $email . ' UID:' . $actorId);
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
