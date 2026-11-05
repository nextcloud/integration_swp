/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { recommendedJavascript } from '@nextcloud/eslint-config'

export default [
	...recommendedJavascript,

	{
		languageOptions: {
			globals: {
				appVersion: 'readonly',
			},
		},
	},
	{
		rules: {
			'jsdoc/require-jsdoc': 'off',
			'jsdoc/tag-lines': 'off',
			'vue/first-attribute-linebreak': 'off',
			'vue/no-v-model-argument': 'off',
			'vue/no-boolean-default': 'off',

			'no-console': 'off',
			'perfectionist/sort-imports': 'off',
			'perfectionist/sort-named-imports': 'off',
			'@stylistic/arrow-parens': 'off',
			'@stylistic/max-statements-per-line': 'off',
			'vue/new-line-between-multi-line-property': 'off',
			'vue/max-attributes-per-line': 'off',
			'vue/attribute-hyphenation': 'off',
			'vue/v-on-event-hyphenation': 'off',
		},
	},
]
