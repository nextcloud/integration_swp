import { imagePath } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'

const navCategoriesToDelete = loadState('integration_swp', 'hidden-activities')

const urlStart = window.location.protocol + '//' + window.location.host
const categoryToIconUrl = {
	contacts: urlStart + imagePath('core', 'places/contacts.svg'),
	calendar: urlStart + imagePath('core', 'places/calendar.svg'),
	calendar_todo: urlStart + imagePath('core', 'places/calendar.svg'),
	files_sharing: urlStart + imagePath('core', 'actions/share.svg'),
}
const iconUrls = navCategoriesToDelete.map(c => {
	return categoryToIconUrl[c] ?? null
}).filter(c => c !== null)

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

function deleteNavigationItems() {
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
	deleteNavigationItems()

	// delete entries when changing category
	const navLinks = document.querySelectorAll('#app-navigation > ul > li > a')
	navLinks.forEach(elem => {
		elem.addEventListener('click', (e) => {
			scheduleDeleteEntries()
		})
	})
})
