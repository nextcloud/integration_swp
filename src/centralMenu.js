/**
 * @copyright Copyright (c) 2021 Julien Veyssier <eneiluj@posteo.net>
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 *
 * @license AGPL-3.0-or-later
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

import './bootstrap.js'
import { loadState } from '@nextcloud/initial-state'
import { imagePath } from '@nextcloud/router'

import Vue from 'vue'
import AppMenu from './components/AppMenu.vue'

const DEBUG = true

export function makeCentralMenu() {
	// const portalUrl = loadState('integration_phoenix', 'portal-url')
	// if (DEBUG) console.debug('PORTAL URL', portalUrl)
	const menu = loadState('integration_phoenix', 'menu-json')
	if (DEBUG) console.debug('menu json :::', menu)
	// const menuTabnameAttribute = loadState('integration_phoenix', 'menu-tabname-attribute')
	// if (DEBUG) console.debug('menu tabname', menuTabnameAttribute)

	if (menu !== null) {
		console.debug(document.querySelector('#header'))
		console.debug(document.querySelector('#header nav'))
		console.debug(document.querySelector('#header nav.app-menu ul.app-menu-main'))

		const headerLeft = document.querySelector('#header .header-left')
		const el = document.createElement('div')
		headerLeft.append(el)

		const View = Vue.extend(AppMenu)
		new View({
			// propsData: { title: widget.title },
		}).$mount(el)

		const headerLogo = document.querySelector('#header .header-left .logo-icon')
		const imageUrl = imagePath('integration_phoenix', 'phoenix_suite_logo-Assets/SVG/phoenix_suite_logo')
		headerLogo.setAttribute('src', imageUrl)
	}
}
