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
	const mailUrl = OCP.InitialState.loadState('sps_bmi', 'mail-url')
	console.debug('MAIL URL', mailUrl)
	// const count = OCP.InitialState.loadState('sps_bmi', 'unread-counter')
	const count = 0

	var renderHeader = function () {
		var icon = document.createElement('div');
		icon.classList = 'icon-mail';
		var badge = document.createElement('span');
		var hasUnread = (count > 0);
		badge.classList = 'unread-badge' + (hasUnread ? ' has-unread' : '');
		badge.textContent = count;

		var label = document.createElement('div');
		label.textContent = t('core', 'Email');

		var parentMailWrapper = document.createElement('div');
		parentMailWrapper.id = "contactsmenu";
		var mailWrapper = document.createElement('a');
		mailWrapper.href = mailUrl;
		mailWrapper.classList = 'sps_bmi_wrapper';
		mailWrapper.appendChild(icon);
		mailWrapper.appendChild(badge);
		mailWrapper.appendChild(label);

		parentMailWrapper.appendChild(mailWrapper);
		return parentMailWrapper;
	}

	document.querySelector('.header-right').insertBefore(renderHeader(), document.getElementById('settings'));

	// override click on mailto: links
	const body = document.querySelector('body')
	body.addEventListener('click', (e) => {
		console.debug('click on', e.target.tagName)
		if (e.target.tagName === 'A') {
			const link = e.target
			const href = link.getAttribute('href')
			//const href = 'mailto:plop@plop.net,second@lala.org?'
			//	+ 'subject=Give%20me%20love'
			//	+ '&body=the%20body'
			//	+ '&cc=plopCC@plop.net,secondCC@lala.org'
			//	+ '&bcc=plopBCC@plop.net,secondBCC@lala.org'
			if (href.match(/^mailto:/i)) {
				e.preventDefault()
				const url = new URL(href)
				const targetMails = url.pathname.split(',')
				const body = url.searchParams.get('body')
				const subject = url.searchParams.get('subject')
				const cc = url.searchParams.get('cc')
				const bcc = url.searchParams.get('bcc')

				const newUrl = new URL(mailUrl)
				// TODO adapt this to OX webmail params
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
				window.open(newUrl.href, '_blank')
			}
		}
	})
})()
