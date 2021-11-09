# Nextcloud app for OpenXChange integration

## Implemented features:
- Unread email counter
- Address book search

## Requirements
- Required OIDC connection to be setup

## OIDC token handling

During login the access_token and refresh token are passed by the user_oidc app to the nmc_spica app thorugh a dispatched event. sps_bmi will request a fresh token and regularly refresh it with the refresh token that was initially provided by the OpenID Connect login.

## Configuration:

Configure OX API endpoint:

	occ config:app:set sps_bmi ox-baseurl --value="https://my.ox.instance"
	occ config:app:set sps_bmi ox-appid --value="my-app-id"
	occ config:app:set sps_bmi ox-appsecret --value="my-secret-key"

Setting a webmail url:

	occ config:app:set sps_bmi webmail-url --value="https://my.ox.instance/webmail"

## Local testing

A refresh token needs to be obtained from a system connected to Telekom Login. This can be done in debug mode (e.g. on dev2 provided by T-Systems) when browsing the https://dev2.next.magentacloud.de/apps/nmc_spica/ url as the logged in user.

Even without using Telekom Login, this app can be tested by manually providing a valid user token through app config:

	export REFRESH_TOKEN="RT2:8323c845-328d-4535-81d6-985787516979:945c6cce-6595-48eb-9312-7ab0aac112e3"
	export SPICA_TOKEN=`curl -X POST https://accounts.login00.idm.ver.sul.t-online.de/oauth2/tokens -d "grant_type=refresh_token&client_id=$OIDC_CLIENT_ID&client_secret=$OIDC_CLIENT_SECRET&refresh_token=$REFRESH_TOKEN&scope=spica" | jq -r .access_token`
	occ config:app:set nmc_spica spica-usertoken --value="$SPICA_TOKEN"

