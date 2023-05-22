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
	<NcActionLink
		:aria-label="entry.display_name"
		:href="entry.link"
		:target="entry.target"
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
import NcActionLink from '@nextcloud/vue/dist/Components/NcActionLink.js'

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
		}
	},
	computed: {
		iconUrl() {
			return this.proxyImage
				? generateUrl('/apps/integration_swp/icon?itemId={itemId}', { itemId: this.entry.identifier })
				: this.entry.icon_url
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
