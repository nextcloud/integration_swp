/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

export function disablePersonalSettingsFields() {
	// personal settings
	const inputs = document.querySelectorAll('#personal-settings input')
	inputs.forEach((element) => {
		if (element.getAttribute('id') === 'enable-profile') {
			element.setAttribute('disabled', 'disabled')
		}
		element.setAttribute('readonly', 'readonly')
	})
	// do not disable language settings which are <select> tags
	/*
	const selects = document.querySelectorAll('#personal-settings select')
	selects.forEach((element) => {
		element.setAttribute('disabled', 'disabled')
	})
	*/
	const textareas = document.querySelectorAll('#personal-settings textarea')
	textareas.forEach((element) => {
		element.setAttribute('readonly', 'readonly')
	})
}
