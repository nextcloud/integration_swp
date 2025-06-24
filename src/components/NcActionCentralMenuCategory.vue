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
import NcActionText from '@nextcloud/vue/components/NcActionText'

export default {
	name: 'NcActionCentralMenuCategory',
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
:deep(.action-text) {
	opacity: 1 !important;
	.action-text__longtext,
	.action-text__text {
		line-height: 20px !important;
		font-weight: bold !important;
		font-size: 17px !important;
		padding: 8px 16px 4px 16px !important;
		max-width: 400px;
	}
}
</style>
