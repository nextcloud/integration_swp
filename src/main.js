/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
 
import { makeCentralMenu, setHeaderLogoUrl } from './centralMenu.js'
import { setMailtoLinks } from './mailtoLinks.js'
import { disablePersonalSettingsFields } from './personalSettings.js'

setHeaderLogoUrl()
makeCentralMenu()
setMailtoLinks()
document.addEventListener('DOMContentLoaded', () => {
	disablePersonalSettingsFields()
})
