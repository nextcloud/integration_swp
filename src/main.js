import { makeCentralMenu } from './centralMenu.js'
import { setMailtoLinks } from './mailtoLinks.js'
import { disablePersonalSettingsFields } from './personalSettings.js'
import '../css/theming.scss'

makeCentralMenu()
setMailtoLinks()
document.addEventListener('DOMContentLoaded', () => {
	disablePersonalSettingsFields()
})
