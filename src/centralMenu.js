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

export function makeCentralMenu() {
	const menu = loadState('integration_swp', 'menu-json')

	if (menu !== null) {
		console.debug(document.querySelector('#header'))
		console.debug(document.querySelector('#header nav'))
		console.debug(document.querySelector('#header nav.app-menu ul.app-menu-main'))

		const headerLeft = document.querySelector('#header .header-left') ?? document.querySelector('#header .header-start')
		const el = document.createElement('div')
		headerLeft.append(el)

		const View = Vue.extend(CentralMenu)
		new View({
			// propsData: { title: widget.title },
		}).$mount(el)
	}
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
