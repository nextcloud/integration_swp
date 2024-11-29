<!--
  - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcActionText
		:aria-label="entry.displayName"
		:aria-current="entry.active ? 'page' : false"
		:href="entry.href"
		class="category">
		{{ entry.display_name }}
	</NcActionText>
</template>

<script>
import NcActionText from '@nextcloud/vue/dist/Components/NcActionText.js'

export default {
	name: 'MenuCategory',
	components: {
		NcActionText,
	},
	props: {
		entry: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
		}
	},
	computed: {
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
::v-deep .action-text {
	opacity: 1 !important;
	&__longtext,
	&__text {
		line-height: 20px !important;
		font-weight: bold !important;
		font-size: 17px !important;
		padding: 8px 16px 4px 16px !important;
	}
}
</style>
