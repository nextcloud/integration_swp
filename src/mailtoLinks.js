/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { loadState } from '@nextcloud/initial-state'

const DEBUG = false

export function setMailtoLinks() {
	const mailUrl = loadState('integration_swp', 'webmail-url')
	const mailTabname = loadState('integration_swp', 'webmail-tabname')
	if (DEBUG) console.debug('MAIL URL :-:-:', mailUrl)
	if (DEBUG) console.debug('MAIL TAB NAME', mailTabname)
	const target = mailTabname ? mailTabname + '' : '_blank'
	if (DEBUG) console.debug('TARGET', target)

	if (mailUrl) {
		const getOXHashMailtoUrl = (mailtoLink) => {
			return mailUrl + '#mailto=' + encodeURIComponent(mailtoLink)
		}

		/*
		const getOXClassicMailtoUrl = (mailtoLink) => {
			const url = new URL(mailtoLink)
			const targetMails = url.pathname.split(',')
			const body = url.searchParams.get('body')
			const subject = url.searchParams.get('subject')
			const cc = url.searchParams.get('cc')
			const bcc = url.searchParams.get('bcc')

			const newUrl = new URL(mailUrl)
			// TODO adapt this to OX webmail params (if we go with this)
			newUrl.searchParams.append('emails', targetMails.join(','))
			if (body) {
				newUrl.searchParams.append('body', body)
			}
			if (subject) {
				newUrl.searchParams.append('subject', subject)
			}
			if (cc) {
				newUrl.searchParams.append('cc', cc)
			}
			if (bcc) {
				newUrl.searchParams.append('bcc', bcc)
			}
			return newUrl.href
		}
		*/

		// override click on mailto: links
		const body = document.querySelector('body')
		body.addEventListener('click', (e) => {
			let link = null
			if (e.target.tagName === 'A') {
				link = e.target
			} else if (e.target.parentElement.tagName === 'A') {
				link = e.target.parentElement
			} else if (e.target.parentElement.parentElement.tagName === 'A') {
				link = e.target.parentElement.parentElement
			} else if (e.target.parentElement.parentElement.parentElement.tagName === 'A') {
				link = e.target.parentElement.parentElement.parentElement
			}
			if (DEBUG) console.debug('CLICK on anything', e.target)
			if (link !== null) {
				if (DEBUG) console.debug('CLICK on link', link)
				const href = link.getAttribute('href')
				/*
				const href = 'mailto:plop@plop.net,second@lala.org?'
					+ 'subject=Give%20me%20love'
					+ '&body=the%20body'
					+ '&cc=plopCC@plop.net,secondCC@lala.org'
					+ '&bcc=plopBCC@plop.net,secondBCC@lala.org'
				*/
				if (href.match(/^mailto:/i)) {
					e.preventDefault()
					e.stopPropagation()
					// according to https://projects.univention.de/xwiki/wiki/sps/view/Product%20%26%20Integration/BMI/5%20-%20Send%20email%20using%20OX/
					window.open(getOXHashMailtoUrl(href), target)
					// window.open(getOXClassicMailtoUrl(href), '_blank')
				}
			}
		})
	}
}
