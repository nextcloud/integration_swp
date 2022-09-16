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

import { loadState } from '@nextcloud/initial-state'
import { generateUrl, imagePath } from '@nextcloud/router'

const DEBUG = false

export function makeCentralMenu() {
	const portalUrl = loadState('integration_phoenix', 'portal-url')
	if (DEBUG) console.debug('PORTAL URL', portalUrl)
	const menuJsonRaw = loadState('integration_phoenix', 'menu-json')
	const menuJson = JSON.parse(menuJsonRaw)
	if (DEBUG) console.debug('menu json :::', menuJson)
	const menuTabnameAttribute = loadState('integration_phoenix', 'menu-tabname-attribute')
	if (DEBUG) console.debug('menu tabname', menuTabnameAttribute)

	const appendElement = (listElement, item, extraClass = null) => {
		const li = document.createElement('li')
		li.classList.add('in-header')
		if (extraClass) {
			li.classList.add(extraClass)
		}
		li.append(item)
		listElement.append(li)
	}

	const appendEntry = (listElement, jsonEntry, proxyImage = true) => {
		const a = document.createElement('a')
		a.setAttribute('href', jsonEntry.link)
		if (jsonEntry.description) {
			a.setAttribute('title', jsonEntry.description)
		}
		if (menuTabnameAttribute && jsonEntry[menuTabnameAttribute]) {
			a.setAttribute('target', jsonEntry[menuTabnameAttribute])
		} else {
			a.setAttribute('target', '_blank')
		}
		// icon
		const icon = document.createElement('img')
		const iconUrl = proxyImage
			? generateUrl('/apps/integration_phoenix/icon?') + 'itemId=' + encodeURIComponent(jsonEntry.identifier)
			: jsonEntry.icon_url
		icon.setAttribute('src', iconUrl)
		/*
		const icon = document.createElement('span')
		icon.classList.add('icon-more')
		icon.classList.add('icon')
		*/

		const text = document.createElement('span')
		text.textContent = jsonEntry.display_name
		a.append(icon)
		a.append(text)
		appendElement(listElement, a, 'elementcontainer')
	}

	const appendCategory = (listElement, jsonCategory) => {
		// category item (only if it has a display name)
		if (jsonCategory.display_name) {
			const categoryElement = document.createElement('span')
			categoryElement.classList.add('category')
			categoryElement.textContent = jsonCategory.display_name
			appendElement(listElement, categoryElement, 'categorycontainer')
		}
		// sub items
		jsonCategory.entries.forEach((entry) => {
			appendEntry(listElement, entry)
		})
	}

	if (menuJson) {
		const itemList = document.querySelector('#navigation #apps ul')
		// clear the menu content
		itemList.innerHTML = ''
		if (portalUrl) {
			// insert the portal entry
			appendEntry(itemList, {
				identifier: 'portal',
				// icon_url: generateUrl('/svg/integration_phoenix/grid?color=000000'),
				icon_url: imagePath('integration_phoenix', 'grid.svg'),
				display_name: 'Portal',
				link: portalUrl,
				description: 'Phoenix portal',
				keywords: 'kw0',
			}, false)
		}
		// insert the json categories
		menuJson.categories.forEach((cat) => {
			appendCategory(itemList, cat)
		})

		const headerLogo = document.querySelector('#header .header-left .logo-icon')
		headerLogo.style.backgroundImage = 'url(\'' + imagePath('integration_phoenix', 'phoenix_suite_logo-Assets/SVG/phoenix_suite_logo') + '\')'
	}
}
