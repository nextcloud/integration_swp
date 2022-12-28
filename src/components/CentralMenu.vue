<!--
  - @copyright Copyright (c) 2022 Julien Veyssier <eneiluj@posteo.net>
  -
  - @author Julien Veyssier <eneiluj@posteo.net>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<nav id="central-app-menu">
		<NcActions class="app-menu-more"
			:container="'#central-app-menu'"
			:aria-label="t('integration_phoenix', 'More apps')">
			<template #icon>
				<GridIcon class="menu-icon" />
			</template>
			<MenuEntry v-if="portalEntry"
				:entry="portalEntry"
				:proxy-image="false" />
			<MenuItem
				v-for="entry in entryList"
				:key="entry.identifier"
				:title="entry.description"
				:entry="entry" />
		</NcActions>
	</nav>
</template>

<script>
import GridIcon from './icons/GridIcon.vue'

import MenuItem from './MenuItem.vue'
import MenuEntry from './MenuEntry.vue'

import NcActions from '@nextcloud/vue/dist/Components/NcActions.js'

import { imagePath } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'

const DEBUG = true

export default {
	name: 'CentralMenu',
	components: {
		MenuEntry,
		GridIcon,
		NcActions,
		MenuItem,
	},
	data() {
		return {
			menuContent: loadState('integration_phoenix', 'menu-json', {}),
			portalUrl: loadState('integration_phoenix', 'portal-url'),
			menuTabnameAttribute: loadState('integration_phoenix', 'menu-tabname-attribute'),
		}
	},
	computed: {
		portalEntry() {
			if (this.portalUrl) {
				return {
					identifier: 'portal',
					icon_url: imagePath('integration_phoenix', 'grid.svg'),
					display_name: t('integration_phoenix', 'Portal'),
					link: this.portalUrl,
					description: t('integration_phoenix', 'Phoenix portal'),
					keywords: 'kw0',
				}
			}
			return null
		},
		entryList() {
			const entries = []
			Object.values(this.menuContent.categories).forEach(c => {
				entries.push({
					identifier: c.identifier,
					display_name: c.display_name,
					isCategory: true,
				})
				entries.push(...c.entries.map(e => {
					return {
						...e,
						isCategory: false,
						target: (this.menuTabnameAttribute && e[this.menuTabnameAttribute])
							? e[this.menuTabnameAttribute]
							: '_blank',
					}
				}))
			})
			return entries
		},
	},
	mounted() {
		if (DEBUG) {
			console.debug('PORTAL URL', this.portalUrl)
			console.debug('menu json :::', this.menuContent)
			console.debug('menu tabname', this.menuTabnameAttribute)
		}
	},
	beforeDestroy() {
	},
	methods: {
	},
}
</script>

<style lang="scss" scoped>
#central-app-menu {
	width: 100%;
	display: flex;
	flex-shrink: 1;
	flex-wrap: wrap;

	.menu-icon {
		color: #1f1f1f;
	}
}

::v-deep .app-menu-more .button-vue--vue-tertiary {
	background-color: transparent !important;

	&:hover {
		background-color: rgba(0, 0, 0, 0.1) !important;
	}

	&:focus-visible {
		opacity: 1;
		//outline: none !important;
	}
}

/*
::v-deep .action.entry.active {
	background-color: unset !important;
}
*/

::v-deep .action-item.action-item--open .action-item__menutoggle {
	background-color: rgba(0, 0, 0, 0.1) !important;
}

::v-deep .v-popper__popper {
	top: 64px !important;
	left: 12px !important;
	transform: unset !important;
	.v-popper__arrow-container {
		display: none;
	}
}
</style>
