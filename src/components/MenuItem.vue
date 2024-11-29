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
import MenuCategory from './MenuCategory.vue'
import MenuEntry from './MenuEntry.vue'

export default {
	name: 'MenuItem',
	components: {
		MenuEntry,
		MenuCategory,
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
			return this.entry.isCategory ? MenuCategory : MenuEntry
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
