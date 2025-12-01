<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Swp\Listener;

use OCA\Swp\AppInfo\Application;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IAppConfig;
use OCP\Util;

/**
 * @implements IEventListener<Event>
 */
class BeforeTemplateRenderedListener implements IEventListener {

	public function __construct(
		private IAppConfig $appConfig,
	) {
	}

	/**
	 * @param Event $event
	 */
	public function handle(Event $event): void {
		if (!$event instanceof BeforeTemplateRenderedEvent) {
			return;
		}

		if ($this->appConfig->getValueString(Application::APP_ID, Application::APP_CONFIG_HIDE_CONTACTS_MENU, '0') === '1') {
			Util::addStyle(Application::APP_ID, 'hide-contactsmenu');
		}
	}
}
