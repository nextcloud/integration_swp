import { imagePath } from '@nextcloud/router'

const urlStart = window.location.protocol + '//' + window.location.host
const iconUrls = [
	urlStart + imagePath('core', 'places/contacts.svg'),
	urlStart + imagePath('core', 'places/calendar.svg'),
]
const navCategoriesToDelete = [
	'contacts',
	'calendar',
	'calendar_todo',
]

function deleteEntries() {
	iconUrls.forEach(url => {
		const imgs = document.querySelectorAll('.activity img[src="' + url + '"]')
		imgs.forEach(item => {
			item.parentNode.parentNode.parentNode.remove()
		})
	})
}

function scheduleDeleteEntries() {
	[500, 1000, 2000, 3000, 4000].forEach(timeout => {
		setTimeout(() => {
			deleteEntries()
		}, timeout)
	})
}

function deleteNavItems() {
	navCategoriesToDelete.forEach(appId => {
		const navLink = document.querySelector('main#content.app-activity a[data-navigation="' + appId + '"]')
		if (navLink) {
			navLink.parentNode.remove()
		}
	})
}

document.addEventListener('DOMContentLoaded', () => {
	// delete entries and nav items on page load
	scheduleDeleteEntries()
	deleteNavItems()

	// delete entries when changing category
	const navLinks = document.querySelectorAll('#app-navigation > ul > li > a')
	navLinks.forEach(elem => {
		elem.addEventListener('click', (e) => {
			scheduleDeleteEntries()
		})
	})
})
