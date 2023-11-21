# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## 1.0.18 – 2023-11-21

### Changed

- update php-jwt and make adjustments

## 1.0.17 – 2023-10-02

### Fixed
- add support for encrypted oidc provider secret (user_oidc >= 1.3.3)

## 1.0.16 – 2023-08-31
### Added
- option to toggle header menu overriding

## 1.0.14 – 2023-04-06
### Added
- backport custom logo URL feature (make custom logo url configurable with app config 'logo-url', fallback to phoenix one)

### Changed
- avoid showing theming logo before replacing it with custom logo

## 1.0.13 – 2023-04-06
### Added
- app setting to toggle phoenix logo, theming one is used if disabled

## 1.0.12 – 2023-02-17
### Fixed
- duplicates when creating recent contacts in OX

## 1.0.11 – 2023-02-08
### Fixed
- Collabora branding for tablets

## 1.0.10 – 2023-02-07
### Fixed
- Collabora branding for tablets

## 1.0.9 – 2023-01-24
### Fixed
- contact search

## 1.0.8 – 2023-01-24
### Changed
- disable cache if system config debug enabled

### Fixed
- navigation height in Files

## 1.0.6 – 2023-01-23
### Fixed
- load style earlier
- fix tooltip position

## 1.0.5 – 2023-01-12
### Fixed
- generic parsing of Ox contact search response (with or without 'data' prop)

## 1.0.4 – 2023-01-12
### Fixed
- fix the header notification icon, use the real one (inverted)

## 1.0.2 – 2022-11-25
### Fixed
- text color in central menu with dark theme

## 1.0.1 – 2022-11-10
### Added
- endpoint (deep link) to create and open Office/Text documents

### Changed
- disable profile checkbox in personal settings
- hide "call" button in Talk's Files sidebar

## 1.0.0 – 2022-09-16
### Added
* first release
