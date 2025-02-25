<!--
  - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<nav id="central-app-menu" :class="{ 'central-app-menu--right': location === 'right' }">
		<NcActions class="app-menu-more"
			:container="'#central-app-menu'"
			:class="{ 'app-menu-more--right': location === 'right', 'app-menu-more--left': location === 'left' }"
			:aria-label="t('integration_swp', 'More apps')">
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
	props: {
		location: {
			type: String,
			required: false,
			default: () => 'left',
		},
	},
	data() {
		return {
			menuContent: loadState('integration_swp', 'menu-json', {}),
			portalUrl: loadState('integration_swp', 'portal-url'),
			menuTabnameAttribute: loadState('integration_swp', 'menu-tabname-attribute'),
		}
	},
	computed: {
		portalEntry() {
			if (this.portalUrl) {
				return {
					identifier: 'portal',
					icon_url: imagePath('integration_swp', 'grid.svg'),
					display_name: t('integration_swp', 'Portal'),
					link: this.portalUrl,
					description: t('integration_swp', 'Sovereign Workplace portal'),
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
		color: var(--color-primary-text);
	}
}

.central-app-menu--right {
	width: unset !important;
}

::v-deep .app-menu-more .button-vue--vue-tertiary {
	background-color: transparent !important;
	height: 64px;
	border-radius: 0;

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
	.v-popper__inner {
		padding: 0 !important;
		max-height: calc(80vh - 16px) !important;
		ul {
			padding: 12px 0;
		}
	}
}

::v-deep(.app-menu-more--right ~ .v-popper__popper) {
	left: unset !important;
	right: 12px !important;
}

::v-deep(.app-menu-more--right .button-vue--vue-tertiary) {
	width: 100%;
	max-width: 50px;
}
</style>
