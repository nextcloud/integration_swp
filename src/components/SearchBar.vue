<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div class="swp-search-bar">
		<NcButton v-if="!supportsLocalSearch"
			class="central-search-button"
			type="secondary"
			:aria-label="t('integration_swp', 'Search')"
			@click="openOriginalUnifiedSearchModal">
			<template #icon>
				<Magnify class="central-search-button__icon" :size="18" />
			</template>
			<span class="central-search-button__text">
				{{ t('integration_swp', 'Search') }}
			</span>
		</NcButton>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import Magnify from 'vue-material-design-icons/Magnify.vue'

export default {
	name: 'SearchBar',
	components: {
		NcButton,
		Magnify,
	},
	data() {
		return {
			toggleLocalSearch: false,
		}
	},
	computed: {
		supportsLocalSearch() {
			const providerPaths = ['/settings/users', '/apps/deck', '/settings/apps']
			return providerPaths.some((path) => window.location.pathname.includes(path))
		},
	},
	methods: {
		openOriginalUnifiedSearchModal() {
			const searchBtn = document.querySelector('#header .unified-search-menu > button') ?? document.querySelector('#header .unified-search__button')
			if (searchBtn) {
				searchBtn.click()
				if (this.supportsLocalSearch) {
					this.toggleLocalSearch = !this.toggleLocalSearch
					this.$nextTick(() => {
						this.$refs.searchInput.focus()
					})
				}
			}
		},
	},
}
</script>

<style lang="scss">
.swp-search-bar {
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 1;
	flex-wrap: wrap;

	.central-search-input {
		max-width: 400px;

		& .input-field__main-wrapper {
			justify-content: center;
		}
	}

	button.central-search-button {
		width: 100%;
		max-width: 400px;
		max-height: 30px;
		border: 2px solid #e9ebee;
		background-color: #f6f7f9;
		padding: 0;
		margin: 0;

		&:hover {
			background-color: #e9ebee !important;
		}

		& > .button-vue__wrapper {
			position: relative;

			& > .button-vue__icon {
				position: absolute;
				left: 5px;
			}

			& > .button-vue__text {
				font-weight: 400;
			}
		}
	}
}
</style>
