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
import {
	generateUrl,
	// imagePath,
} from '@nextcloud/router'

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

export function setHeaderLogoUrl() {
	const useCustomLogo = loadState('integration_phoenix', 'use-custom-logo')
	const logo = document.querySelector('#header #nextcloud')
	if (useCustomLogo) {
		// add the custom one
		const img = document.createElement('img')
		console.debug('setHeaderLogoUrl', logo)
		console.debug('setHeaderLogoUrl', logo.style)
		// const src = imagePath('integration_phoenix', 'phoenix_suite_logo-Assets/SVG/phoenix_suite_logo.svg')
		const src = generateUrl('/apps/integration_phoenix/logo')
		img.setAttribute('src', src)
		img.classList.add('custom-logo')
		logo.append(img)
	} else {
		// hide theming logo
		const themingLogo = logo.querySelector('.logo')
		themingLogo.classList.add('enabled')
	}

	// set logo link target
	const portalUrl = loadState('integration_phoenix', 'portal-url')
	if (portalUrl) {
		logo.setAttribute('href', portalUrl)
		logo.setAttribute('target', '_blank')
	}
}
