# Nextcloud Sovereign Workplace (Phoenix) integration

## Implemented features:
- Unread email counter
- Address book search
- Central navigation
- Unified design

## Requirements
- Required OIDC connection with user_oidc to be setup

## OIDC token handling

During login the access_token and refresh token are passed by the user_oidc app to the integration_swp app through a dispatched event. integration_swp will request a fresh token and regularly refresh it with the refresh token that was initially provided by the OpenID Connect login.

## Configuration:

Configure OX API endpoint:

	occ config:app:set integration_swp ox-baseurl --value="https://my.ox.instance"

Setting a webmail url:

	occ config:app:set integration_swp webmail-url --value="https://my.ox.instance/webmail"

Set the main content style to square corners and remove the margins:

	occ config:app:set integration_swp square-corners --value="1"

Set the default user theme (but still allow the users to change it), default value is "light":

	occ config:app:set integration_swp default-user-theme --value="THEME"

THEME can be `light`, `dark`, `light-highcontrast` or `dark-highcontrast`.

Set the top-left logo:

	# Do we want to use a custom logo? (1 or 0) Use the theming one if 0.
	occ config:app:set integration_swp use-custom-logo --value="1"
	# The custom logo image URL (default is the local /img/phoenix_suite_logo-Assets/SVG/phoenix_suite_logo.svg file)
	occ config:app:set integration_swp logo-image-url --value="https://logos.org/my/logo.svg"
	# CSS properties for the logo image
	occ config:app:set integration_swp logo-width --value="63%"
	occ config:app:set integration_swp logo-height --value="auto"
	# href of the logo link
	occ config:app:set integration_swp logo-link-url --value="https://swp.org"
	# target of the logo link
	occ config:app:set integration_swp logo-link-target --value="_blank"
	# title of the logo link
	occ config:app:set integration_swp logo-link-title --value="SWP portal"

## Local testing

Even without using SWP Login, this app can be tested by manually providing a valid OIDC ID token through app config:

	occ config:app:set integration_swp ox-usertoken --value="$OIDC_ID_TOKEN"
