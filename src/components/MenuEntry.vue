<!--
  - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcActionLink
		:aria-label="entry.display_name"
		:href="entry.link"
		:target="target"
		class="entry">
		<template #icon>
			<div class="app-icon">
				<img :src="iconUrl" alt="">
			</div>
		</template>
		{{ entry.display_name }}
	</NcActionLink>
</template>

<script>
import NcActionLink from '@nextcloud/vue/components/NcActionLink'

import { generateUrl } from '@nextcloud/router'

export default {
	name: 'MenuEntry',
	components: {
		NcActionLink,
	},
	props: {
		entry: {
			type: Object,
			required: true,
		},
		proxyImage: {
			type: Boolean,
			default: true,
		},
	},
	data() {
		return {
			dummyUrl: window.location.protocol + '//' + window.location.host + generateUrl('/apps/integration_swp').replace('/apps/integration_swp', ''),
		}
	},
	computed: {
		iconUrl() {
			return this.proxyImage
				? generateUrl('/apps/integration_swp/icon?itemId={itemId}', { itemId: this.entry.identifier })
				: this.entry.icon_url
		},
		target() {
			// no target if the link points to NC
			if (this.entry.link?.startsWith(this.dummyUrl)) {
				return null
			}
			return this.entry.target
		},
	},
	mounted() {
	},
	beforeDestroy() {
	},
	methods: {
	},
}
</script>

<style lang="scss" scoped>
::v-deep .action-link {
	opacity: 1 !important;

	&:hover {
		background-color: rgba(0, 0, 0, 0.1);
	}

	&__text,
	&__longtext {
		max-width: 352px !important;
		line-height: 20px !important;
		align-self: center;
	}
}

.app-icon {
	padding: 12px 16px;
	display: flex;
	img {
		height: 20px;
		width: 20px;
	}
}
</style>
