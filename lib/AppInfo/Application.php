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

namespace OCA\Swp\AppInfo;

use OCA\Files_Sharing\Event\BeforeTemplateRenderedEvent;
use OCA\Swp\Listener\PublicShareTemplateLoader;
use OCA\Swp\Listener\TokenObtainedEventListener;
use OCA\Swp\Listener\ContactInteractionSpsListener;
use OCA\Swp\Service\MenuService;
use OCA\Swp\Service\TokenService;
use OCA\Swp\Service\OxMailService;
use OCA\Swp\OxAddressBook;
use OCA\UserOIDC\Event\TokenObtainedEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Services\IInitialState;
use OCP\Contacts\Events\ContactInteractedWithEvent;
use OCP\Contacts\IManager;
use OCP\IConfig;
use OCP\IL10N;
use OCP\INavigationManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'integration_swp';
	public const DEBUG = true;

	public const USER_CONFIG_KEY_UNREAD_COUNT = 'unread-count';

	public const APP_CONFIG_DEFAULT_USER_THEME = 'default-user-theme';
	public const APP_CONFIG_DEFAULT_USER_THEME_DEFAULT = 'light';
	public const APP_CONFIG_SQUARE_CORNERS = 'square-corners';
	public const APP_CONFIG_USE_CUSTOM_LOGO = 'use-custom-logo';
	public const APP_CONFIG_LOGO_IMAGE_URL = 'logo-image-url';
	public const APP_CONFIG_LOGO_WIDTH = 'logo-width';
	public const APP_CONFIG_LOGO_HEIGHT = 'logo-height';
	public const APP_CONFIG_LOGO_LINK_URL = 'logo-link-url';
	public const APP_CONFIG_LOGO_LINK_TARGET = 'logo-link-target';
	public const APP_CONFIG_LOGO_LINK_TITLE = 'logo-link-title';
	public const APP_CONFIG_PORTAL_URL = 'portal-url';
	public const APP_CONFIG_WEBMAIL_URL = 'webmail-url';
	public const APP_CONFIG_WEBMAIL_TABNAME = 'webmail-tabname';
	public const APP_CONFIG_OX_URL = 'ox-baseurl';

	public const APP_CONFIG_ACTIVITY_CATEGORY_BLACKLIST = 'hidden-activities';
	public const APP_CONFIG_ACTIVITY_CATEGORY_BLACKLIST_DEFAULT = 'contacts,calendar,calendar_todo';

	public const APP_CONFIG_NAVIGATION_URL = 'navigation-json-url';
	public const APP_CONFIG_NAVIGATION_AUTH_TYPE = 'navigation-json-auth-type';
	public const APP_CONFIG_NAVIGATION_SHARED_SECRET = 'navigation-json-api-secret';
	public const APP_CONFIG_NAVIGATION_USERNAME_ATTRIBUTE = 'navigation-json-username-attribute';

	public const APP_CONFIG_MENU_TABNAME_ATTRIBUTE = 'menu-tabname-attribute';

	public const APP_CONFIG_CACHE_TTL_MAIL = 'cache-ttl-mail';
	public const APP_CONFIG_CACHE_TTL_MAIL_DEFAULT = 60;
	public const APP_CONFIG_CACHE_TTL_CONTACTS = 'cache-ttl-contacts';
	public const APP_CONFIG_CACHE_TTL_CONTACTS_DEFAULT = 600;
	public const APP_CONFIG_CACHE_NAVIGATION_JSON = 'cache-navigation-json';
	public const APP_CONFIG_CACHE_NAVIGATION_JSON_DEFAULT = 3600;

	public function __construct() {
		parent::__construct(self::APP_ID, []);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(TokenObtainedEvent::class, TokenObtainedEventListener::class);
		$context->registerEventListener(ContactInteractedWithEvent::class, ContactInteractionSpsListener::class);
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, PublicShareTemplateLoader::class);
	}

	public function boot(IBootContext $context): void {
		$context->injectFn(function (
			IInitialState $initialState,
			TokenService $tokenService,
			MenuService $menuService,
			INavigationManager $navigationManager,
			IManager $contactsManager,
			OxAddressBook $oxAddressBook,
			IL10N $l10n,
			OxMailService $unreadService,
			IURLGenerator $urlGenerator,
			IConfig $config,
			IUserSession $userSession,
			IRequest $request,
			$userId
		) {
			if (!$userId) {
				return;
			}

			$token = $tokenService->getToken();
			if ($token === null) {
				// if we don't have a token but we had one once,
				// it means the session (where we store the token) has died
				// so we need to reauthenticate
				if ($config->getUserValue($userId, self::APP_ID, 'had_token_once', '0') === '1') {
					$userSession->logout();
				}
				return;
			}
			// remember that this user had a token once
			$config->setUserValue($userId, self::APP_ID, 'had_token_once', '1');

			// set the theme to light once (make it the default one but allow users to change it)
			if ($config->getUserValue($userId, self::APP_ID, 'theme_set', '0') !== '1') {
				$defaultUserTheme = $config->getUserValue($userId, self::APP_ID, self::APP_CONFIG_DEFAULT_USER_THEME, self::APP_CONFIG_DEFAULT_USER_THEME_DEFAULT);
				$config->setUserValue($userId, 'theming', 'enabled-themes', '["' . $defaultUserTheme . '"]');
				$config->setUserValue($userId, self::APP_ID, 'theme_set', '1');
			}

			$contactsManager->registerAddressBook($oxAddressBook);

			if ($token->isExpired()) {
				$tokenService->reauthenticate();
			}

			// as we get the menu items with a central navigation service, this is not necessary anymore
			// $this->registerNavigationItems();

			$initialState->provideLazyInitialState(self::APP_CONFIG_USE_CUSTOM_LOGO, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_USE_CUSTOM_LOGO, '1') === '1';
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_LOGO_LINK_TARGET, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_LOGO_LINK_TARGET, '_blank') ?: '_blank';
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_LOGO_LINK_URL, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_LOGO_LINK_URL);
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_LOGO_LINK_TITLE, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_LOGO_LINK_TITLE);
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_LOGO_WIDTH, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_LOGO_WIDTH);
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_LOGO_HEIGHT, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_LOGO_HEIGHT);
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_PORTAL_URL, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_PORTAL_URL, '');
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_MENU_TABNAME_ATTRIBUTE, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_MENU_TABNAME_ATTRIBUTE, '');
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_WEBMAIL_TABNAME, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_WEBMAIL_TABNAME, '');
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_WEBMAIL_URL, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_WEBMAIL_URL, '');
			});
			$initialState->provideLazyInitialState(self::APP_CONFIG_OX_URL, function () use ($config) {
				return $config->getAppValue(self::APP_ID, self::APP_CONFIG_OX_URL, '');
			});
			$initialState->provideLazyInitialState('menu-json', function () use ($menuService, $token) {
				return $menuService->getMenuJson($token);
			});

			Util::addScript(self::APP_ID, self::APP_ID . '-main');
			Util::addStyle(self::APP_ID, 'theming');
			if ($config->getAppValue(self::APP_ID, self::APP_CONFIG_SQUARE_CORNERS) === '1') {
				Util::addStyle(self::APP_ID, 'square-corners');
			}

			if ($request->getPathInfo() === '/apps/activity/' || $request->getPathInfo() === '/apps/activity') {
				$initialState->provideLazyInitialState(self::APP_CONFIG_ACTIVITY_CATEGORY_BLACKLIST, function () use ($config) {
					$activitiesString = $config->getAppValue(
						self::APP_ID,
						self::APP_CONFIG_ACTIVITY_CATEGORY_BLACKLIST,
						self::APP_CONFIG_ACTIVITY_CATEGORY_BLACKLIST_DEFAULT) ?: self::APP_CONFIG_ACTIVITY_CATEGORY_BLACKLIST_DEFAULT;
					if ($activitiesString) {
						return explode(',', $activitiesString);
					} else {
						return [];
					}
				});
				Util::addScript(self::APP_ID, self::APP_ID . '-activity');
			}
		});
	}

	private function registerNavigationItems(): void {
		$container = $this->getContainer();
		$container->get(INavigationManager::class)->add(function () use ($container) {
			$urlGenerator = $container->get(IURLGenerator::class);
			$l10n = $container->get(IL10N::class);
			return [
				'id' => 'ox-mail',
				'order' => 0,
				'href' => 'https://nextcloud.com',
				'icon' => $urlGenerator->imagePath(self::APP_ID, 'grid.svg'),
				'name' => $l10n->t('OX Mail'),
			];
		});
	}
}
