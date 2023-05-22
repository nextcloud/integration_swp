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

## Local testing

Even without using SWP Login, this app can be tested by manually providing a valid OIDC ID token through app config:

	occ config:app:set integration_swp ox-usertoken --value="$OIDC_ID_TOKEN"
