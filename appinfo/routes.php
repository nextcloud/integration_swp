<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#createDocument', 'url' => '/office-create/{ext}', 'verb' => 'GET'],
		['name' => 'page#getLogo', 'url' => '/logo', 'verb' => 'GET'],
		['name' => 'Menu#getMenuEntryIcon', 'url' => '/icon', 'verb' => 'GET'],
	],
];
