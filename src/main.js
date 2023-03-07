import { makeCentralMenu, setHeaderLogoUrl } from './centralMenu.js'
import { setMailtoLinks } from './mailtoLinks.js'
import { disablePersonalSettingsFields } from './personalSettings.js'

setHeaderLogoUrl()
makeCentralMenu()
setMailtoLinks()
document.addEventListener('DOMContentLoaded', () => {
	disablePersonalSettingsFields()
})
