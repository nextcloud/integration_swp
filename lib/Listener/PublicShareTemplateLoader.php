<?php

declare(strict_types=1);

/**
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 * @copyright Julien Veyssier 2022
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Swp\Listener;

use OCA\Files_Sharing\Event\BeforeTemplateRenderedEvent;
use OCA\Swp\AppInfo\Application;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\Util;

/**
 * Helper class to extend the "publicshare" template from the server.
 */
class PublicShareTemplateLoader implements IEventListener {

	public function __construct(
		private IConfig $config,
		private IInitialState $initialState,
	) {
	}

	/**
	 * @param Event $event
	 */
	public function handle(Event $event): void {
		if (!$event instanceof BeforeTemplateRenderedEvent) {
			return;
		}

		if ($event->getScope() !== null) {
			// If the event has a scope, it's not the default share page, but e.g. authentication
			return;
		}

		Util::addStyle(Application::APP_ID, 'public');

		// optionally override style of public pages
		if ($this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_CUSTOM_STYLE_PUBLIC_PAGES, '1') === '1') {
			Util::addScript(Application::APP_ID, Application::APP_ID . '-public');

			Util::addStyle(Application::APP_ID, 'theming');
			if ($this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_SQUARE_CORNERS, '1') === '1') {
				Util::addStyle(Application::APP_ID, 'square-corners');
			}
			if ($this->config->getAppValue(Application::APP_ID, Application::APP_CONFIG_OVERRIDE_HEADER_COLOR, '1') === '1') {
				Util::addStyle(Application::APP_ID, 'color');
			}

			$config = $this->config;
			$this->initialState->provideLazyInitialState(Application::APP_CONFIG_USE_CUSTOM_LOGO, function () use ($config) {
				return $config->getAppValue(Application::APP_ID, Application::APP_CONFIG_USE_CUSTOM_LOGO, '1') === '1';
			});
			$this->initialState->provideLazyInitialState(Application::APP_CONFIG_LOGO_LINK_TARGET, function () use ($config) {
				return $config->getAppValue(Application::APP_ID, Application::APP_CONFIG_LOGO_LINK_TARGET, '_blank') ?: '_blank';
			});
			$this->initialState->provideLazyInitialState(Application::APP_CONFIG_LOGO_LINK_URL, function () use ($config) {
				return $config->getAppValue(Application::APP_ID, Application::APP_CONFIG_LOGO_LINK_URL);
			});
			$this->initialState->provideLazyInitialState(Application::APP_CONFIG_LOGO_LINK_TITLE, function () use ($config) {
				return $config->getAppValue(Application::APP_ID, Application::APP_CONFIG_LOGO_LINK_TITLE);
			});
			$this->initialState->provideLazyInitialState(Application::APP_CONFIG_LOGO_WIDTH, function () use ($config) {
				return $config->getAppValue(Application::APP_ID, Application::APP_CONFIG_LOGO_WIDTH);
			});
			$this->initialState->provideLazyInitialState(Application::APP_CONFIG_LOGO_HEIGHT, function () use ($config) {
				return $config->getAppValue(Application::APP_ID, Application::APP_CONFIG_LOGO_HEIGHT);
			});
		}
	}
}
