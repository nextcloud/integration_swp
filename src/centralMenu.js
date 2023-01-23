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

import Vue from 'vue'
import CentralMenu from './components/CentralMenu.vue'

export function makeCentralMenu() {
	const menu = loadState('integration_phoenix', 'menu-json')

	if (menu !== null) {
		console.debug(document.querySelector('#header'))
		console.debug(document.querySelector('#header nav'))
		console.debug(document.querySelector('#header nav.app-menu ul.app-menu-main'))

		const headerLeft = document.querySelector('#header .header-left')
		const el = document.createElement('div')
		headerLeft.append(el)

		const View = Vue.extend(CentralMenu)
		new View({
			// propsData: { title: widget.title },
		}).$mount(el)
	}
}
