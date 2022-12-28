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
