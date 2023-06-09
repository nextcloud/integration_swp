# Nextcloud Sovereign Workplace (Phoenix) integration

## Implemented features

- Address book search
- Central navigation
- Unified style

## Requirements

- Required OIDC connection with user_oidc to be setup

## OIDC token handling

During login the access_token and refresh token are passed by the user_oidc app to the integration_swp app through a dispatched event.
integration_swp will request a fresh token and regularly refresh it with the refresh token that was initially provided by the OpenID Connect login.

## Configuration:

### OpenXChange

Configure OpenXChange base URL (to contact its API):

	occ config:app:set integration_swp ox-baseurl --value="https://my.ox.instance"

Set a webmail url (will override the mailto: links click and open them in a new tab):

	occ config:app:set integration_swp webmail-url --value="https://my.ox.instance/webmail"

Set the webmail tab name (target):

	occ config:app:set integration_swp webmail-tabname --value="swp-webmail"

Set the cache duration for OX contact search requests (default: 600, 10 minutes):

	occ config:app:set integration_swp cache-ttl-contacts --value="12"

### Central menu

Set the portal URL (will add an entry in the central menu):

	occ config:app:set integration_swp portal-url --value="https://portal.org"

Set the central menu Json content URL:

	occ config:app:set integration_swp navigation-json-url --value="https://my.central/menu.json"

Set the authentication type when requesting the menu json content (basic or bearer, default: basic)

	occ config:app:set integration_swp navigation-json-auth-type --value="basic"

Set the shared secret to access the central menu json endpoint.
Will be used as a password if basic auth type is "basic".
Will be used as bearer token if auth type is "bearer".

	occ config:app:set integration_swp navigation-json-api-secret --value="abc123456"

Set the OIDC token attribute which value should be used as username when requesting the central menu json endpoint.
(default: "preferred_username")
Will be used as username if auth type is basic.
Will be used in the X-Ucs-Username HTTP header is auth type is bearer.

	occ config:app:set integration_swp navigation-json-username-attribute --value="preferred_username"

Choose which central menu item attribute should be used as target when opening entries.
(default: undefined, target will be "_blank")

	occ config:app:set integration_swp menu-tabname-attribute --value="myAttr"

Set the menu json content cache duration in seconds (default: 3600, one hour):

	occ config:app:set integration_swp cache-navigation-json --value="10"

### Unified style

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

Configure which activity types should be hidden (comma separated, default is "contacts,calendar,calendar_todo"):

	occ config:app:set integration_swp hidden-activities --value="contacts,calendar,calendar_todo"

## Local testing

You can get the current OIDC token information on this page:

	https://my.nextcloud.org/index.php/apps/integration_swp

Even without using SWP Login, the integration with OpenXChange can be tested by manually providing a valid OIDC ID token through app config:

	occ config:app:set integration_swp ox-usertoken --value="$OIDC_ID_TOKEN"
