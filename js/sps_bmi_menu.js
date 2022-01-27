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

(function() {
	const menuJsonRaw = OCP.InitialState.loadState('sps_bmi', 'menu-json')
	const menuJson = JSON.parse(menuJsonRaw)
	console.debug('menu json', menuJson)

	const appendElement = function (listElement, item) {
		const li = document.createElement('li');
		li.classList.add('in-header')
		li.append(item)
		listElement.append(li)
	}

	const appendEntry = function (listElement, jsonEntry, proxyImage = true) {
		const a = document.createElement('a');
		a.setAttribute('href', jsonEntry.link)
		if (jsonEntry.description) {
			a.setAttribute('title', jsonEntry.description)
		}
		// icon
		const icon = document.createElement('img');
		const imgUrl = proxyImage
			? OC.generateUrl('/apps/sps_bmi/image?') + 'url=' + encodeURIComponent(jsonEntry.icon_url)
			: jsonEntry.icon_url
		icon.setAttribute('src', imgUrl)
		/*
		const icon = document.createElement('span');
		icon.classList.add('icon-more')
		icon.classList.add('icon')
		*/

		const text = document.createElement('span');
		text.textContent = jsonEntry.display_name
		a.append(icon)
		a.append(text)
		appendElement(listElement, a)
	}

	const appendCategory = function (listElement, jsonCategory) {
		// category item
		const categoryElement = document.createElement('span');
		categoryElement.classList.add('category')
		categoryElement.textContent = jsonCategory.display_name
		appendElement(listElement, categoryElement)
		// sub items
		jsonCategory.entries.forEach((entry) => {
			appendEntry(listElement, entry)
		})
	}

	if (menuJson) {
		const itemList = document.querySelector('#navigation #apps ul')
		// clear the menu content
		itemList.innerHTML = ''
		// insert the portal entry
		appendEntry(itemList, {
			identifier: 'portal',
			icon_url: OC.generateUrl('/svg/sps_bmi/grid?color=000000'),
			display_name: 'Portal',
			link: 'https://duckduckgo.com/one',
			description: '0-1',
			keywords: 'kw0'
		}, false)
		// insert the json categories
		menuJson.categories.forEach((cat) => {
			appendCategory(itemList, cat)
		})
	}
})()