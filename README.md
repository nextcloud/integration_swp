# Nextcloud app for OpenXChange integration

## Implemented features:
- Unread email counter
- Address book search

## Requirements
- Required OIDC connection to be setup

## OIDC token handling

During login the access_token and refresh token are passed by the user_oidc app to the sps_bmi app through a dispatched event. sps_bmi will request a fresh token and regularly refresh it with the refresh token that was initially provided by the OpenID Connect login.

## Configuration:

Configure OX API endpoint:

	occ config:app:set sps_bmi ox-baseurl --value="https://my.ox.instance"

Setting a webmail url:

	occ config:app:set sps_bmi webmail-url --value="https://my.ox.instance/webmail"

## Local testing

Even without using Univention Login, this app can be tested by manually providing a valid OIDC ID token through app config:

	occ config:app:set sps_bmi ox-usertoken --value="$OIDC_ID_TOKEN"
