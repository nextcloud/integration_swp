import { makeCentralMenu } from './centralMenu'
import { setMailtoLinks } from './mailtoLinks'
import { disablePersonalSettingsFields } from './personalSettings'
import '../css/theming.scss'

makeCentralMenu()
setMailtoLinks()
document.addEventListener('DOMContentLoaded', () => {
	disablePersonalSettingsFields()
})
