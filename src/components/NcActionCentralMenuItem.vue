<!--
  - SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<component
		:is="myComponent"
		:entry="entry" />
</template>

<script>
import NcActionCentralMenuEntry from './NcActionCentralMenuEntry.vue'
import NcActionCentralMenuCategory from './NcActionCentralMenuCategory.vue'

export default {
	name: 'NcActionCentralMenuItem',
	components: {
		NcActionCentralMenuEntry,
		NcActionCentralMenuCategory,
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
		myComponent() {
			return this.entry.isCategory ? NcActionCentralMenuCategory : NcActionCentralMenuEntry
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
// nothing yet
</style>
