/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import './bootstrap.js'
import { loadState } from '@nextcloud/initial-state'
import {
	generateUrl,
	// imagePath,
} from '@nextcloud/router'

import Vue from 'vue'
import CentralMenu from './components/CentralMenu.vue'
import SearchBar from './components/SearchBar.vue'

export function makeCentralMenu() {
	const menu = loadState('integration_swp', 'menu-json')

	if (menu !== null) {
		console.debug(document.querySelector('#header'))
		console.debug(document.querySelector('#header nav'))
		console.debug(document.querySelector('#header nav.app-menu ul.app-menu-main'))

		const headerLeft = document.querySelector('#header .header-left') ?? document.querySelector('#header .header-start')
		const el = document.createElement('div')
		const centralMenuLocation = loadState('integration_swp', 'menu-header-location', 'left')

		if (centralMenuLocation === 'left') {
			headerLeft.append(el)
			const View = Vue.extend(CentralMenu)
			new View().$mount(el)
		} else {
			document.addEventListener('DOMContentLoaded', () => {
				addCentralMenuBeforeUserMenu(el)
			})
		}

		const header = document.querySelector('#header')
		const unifiedSearchMenu = document.querySelector('#header .unified-search-menu') ?? document.querySelector('#header .unified-search__button')
		if (supportsLocalSearch() === false) {
			unifiedSearchMenu.classList.add('central-menu-search-hidden')
		}
		const headerMiddle = document.createElement('div')
		headerMiddle.classList.add('header-middle')
		header.insertBefore(headerMiddle, headerLeft.nextSibling)

		const searchEl = document.createElement('div')
		headerMiddle.append(searchEl)
		const Search = Vue.extend(SearchBar)
		new Search().$mount(searchEl)
	}
}

function supportsLocalSearch() {
	const providerPaths = ['/settings/users', '/apps/deck', '/settings/apps']
	return providerPaths.some((path) => window.location.pathname.includes(path))
}

function addCentralMenuBeforeUserMenu(el, attempt = 0) {
	setTimeout(() => {
		const userMenu = document.querySelector('#header #user-menu')
		if (userMenu) {
			const headerRight = document.querySelector('#header .header-right') ?? document.querySelector('#header .header-end')
			headerRight.insertBefore(el, userMenu)
			const View = Vue.extend(CentralMenu)
			new View({
				propsData: { location: 'right' },
			}).$mount(el)
		} else if (attempt < 5) {
			addCentralMenuBeforeUserMenu(el, attempt + 1) // try again in 500ms
		} else {
			console.error('Could not find user menu to insert central menu')
		}
	}, 500)
}

export function setHeaderLogoUrl() {
	const useCustomLogo = loadState('integration_swp', 'use-custom-logo')
	const logo = document.querySelector('#header #nextcloud')
	const logoWrapper = document.createElement('div')
	logoWrapper.classList.add('logo-wrapper')
	logo.prepend(logoWrapper)

	if (useCustomLogo) {
		// add the custom one
		const img = document.createElement('img')
		console.debug('setHeaderLogoUrl', logo)
		console.debug('setHeaderLogoUrl', logo.style)
		// const src = imagePath('integration_swp', 'phoenix_suite_logo-Assets/SVG/phoenix_suite_logo.svg')
		const src = generateUrl('/apps/integration_swp/logo')
		img.setAttribute('src', src)
		img.classList.add('custom-logo')
		// custom logo size
		const customLogoWidth = loadState('integration_swp', 'logo-width')
		const customLogoHeight = loadState('integration_swp', 'logo-height')
		if (customLogoWidth || customLogoHeight) {
			const width = customLogoWidth ? `width: ${customLogoWidth};` : ''
			const height = customLogoHeight ? `height: ${customLogoHeight};` : ''
			img.setAttribute('style', width + height)
		}
		logoWrapper.prepend(img)

	} else {
		// show theming logo
		const themingLogo = logo.querySelector('.logo')
		themingLogo.classList.add('enabled')
		logoWrapper.append(themingLogo)
	}

	// set logo link target
	const logoLinkUrl = loadState('integration_swp', 'logo-link-url')
	if (logoLinkUrl) {
		logo.setAttribute('href', logoLinkUrl)
	}
	const logoLinkTarget = loadState('integration_swp', 'logo-link-target')
	if (logoLinkTarget) {
		logo.setAttribute('target', logoLinkTarget)
	}
	const logoLinkTitle = loadState('integration_swp', 'logo-link-title')
	if (logoLinkTitle) {
		logo.setAttribute('title', logoLinkTitle)
	}
}
