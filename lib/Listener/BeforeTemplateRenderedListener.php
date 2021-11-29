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


namespace OCA\SpsBmi\Listener;


use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\Util;

class BeforeTemplateRenderedListener implements IEventListener {

	/** @var IConfig */
	private $config;
	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(IConfig $config,
								IURLGenerator $urlGenerator) {
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
	}

	public function handle(Event $event): void {
		if (!$event instanceof BeforeTemplateRenderedEvent) {
			return;
		}

		$linkToCSS = $this->urlGenerator->linkToRoute(
			'sps_bmi.Theming.getStylesheet',
			[
				'v' => $this->config->getAppValue('sps_bmi', 'cachebuster', '0'),
			]
		);
		Util::addHeader(
			'link',
			[
				'rel' => 'stylesheet',
				'href' => $linkToCSS,
			]
		);
	}
}

