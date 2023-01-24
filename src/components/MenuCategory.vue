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
