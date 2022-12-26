import { imagePath } from '@nextcloud/router'
import '../css/theming.scss'
import '../css/public.scss'

const headerLogo = document.querySelector('#header .header-left .logo-icon')
const imageUrl = imagePath('integration_phoenix', 'phoenix_suite_logo-Assets/SVG/phoenix_suite_logo')
headerLogo.setAttribute('src', imageUrl)
