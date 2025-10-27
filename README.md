<!--
  - SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Nextcloud Sovereign Workplace (Phoenix) integration

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/integration_swp)](https://api.reuse.software/info/github.com/nextcloud/integration_swp)

## Implemented features

- Address book search
- Central navigation
- Unified style

## Requirements

- Required OIDC connection with user_oidc to be setup

## OIDC token handling

This app needs a valid OIDC token to get the central menu content and contact OpenXChange. We get this token from the user_oidc app.
Depending on how user_oidc is configured, we either store and refresh the login token ourselves or rely on user_oidc to do so.

If "Store login tokens" is enabled in user_oidc's admin settings, we know we can use the `OCA\UserOIDC\Event\ExternalTokenRequestedEvent`
to ask user_oidc to provide the login token (or a refreshed one) instead of storing this token ourselves (and refreshing it).

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

Set the menu location in header: `left` (default) or `right`:

	occ config:app:set integration_swp menu-header-location --value="right"

### Unified style

Override the header menu background color to white and  the page background color to gray
(independently from the accent color set in theming admin settings). This is enabled by default:

	occ config:app:set integration_swp override-header-color --value="1"

Set the main content style to square corners and remove the margins. This is enabled by default:

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

Hide the contacts top-right menu entry (default=0):

	occ config:app:set integration_swp hide-contacts-menu --value="1"

## Local testing

Enable the apps features (theming, central menu, logo override...) even if the user is not connected via user_oidc:

	occ config:app:set integration_swp debug_mode --value="1"

You can get the current OIDC token information on this page:

	https://my.nextcloud.org/index.php/apps/integration_swp

Even without using SWP Login, the integration with OpenXChange can be tested by manually providing a valid OIDC ID token through app config:

	occ config:app:set integration_swp ox-usertoken --value="$OIDC_ID_TOKEN"
