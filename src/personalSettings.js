export function disablePersonalSettingsFields() {
	// personal settings
	const inputs = document.querySelectorAll('#personal-settings input')
	inputs.forEach((element) => {
		element.setAttribute('readonly', 'readonly')
	})
	// do not disable language settings which are <select> tags
	/*
	const selects = document.querySelectorAll('#personal-settings select')
	selects.forEach((element) => {
		element.setAttribute('disabled', 'disabled')
	})
	*/
	const textareas = document.querySelectorAll('#personal-settings textarea')
	textareas.forEach((element) => {
		element.setAttribute('readonly', 'readonly')
	})
}
