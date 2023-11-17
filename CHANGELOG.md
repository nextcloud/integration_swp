# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## 3.1.3 – 2023-11-17

### Fixed

- fix type issue when decoding JWT token (keys are obtained with a different type in user_oidc)

## 3.1.2 – 2023-11-17

### Fixed

- update firebase/php-jwt to 6.9.0 to make it work with latest user_oidc

## 3.1.1 – 2023-10-02

### Changed

- Update makefile to keep packaging files
- Update property-information npm pkg

### Fixed

- Add support for encrypted oidc provider secret (user_oidc >= 1.3.3)

## 3.1.0 – 2023-08-11

### Added

- New app config to toggle header menu color override

## 3.0.11 – 2023-08-02

### Added

- Add debug_mode app config flag to avoid having to authenticate via user_oidc

## 3.0.10 – 2023-07-17

### Changed

- Adjust release process to public repo

## 3.0.8 – 2023-06-05

### Added

- New `logo-width`, `logo-height`, `logo-link-url`, `logo-link-target` and `logo-link-title` app configs
- Documentation for all app configs in README.md

### Changed

- Renamed `logo-url` app config key to `logo-image-url`
- Update npm pkgs
- Use some Php 8 new syntax (class attribute declaration in constructor)

## 3.0.7 – 2023-05-25

### Added

- app config to choose the user default theme

## 3.0.6 – 2023-05-22

### Changed

- rename the app to integration_swp

## 3.0.5 – 2023-05-22

### Changed

- logo link target URL is now the portal-url app config, target is _blank
- remove rounded corners for #content-vue and central menu dropdown
- bump max NC version to 28

## 3.0.4 – 2023-05-05
### Added
- App config to set square corners on the main content

## 3.0.3 – 2023-04-17
### Added
- 'hidden-activities' app config to choose which activity category to hide

## 3.0.2 – 2023-03-23
### Added
- hide contacts, calendar and calendar_todo activity navigation items

## 3.0.1 – 2023-03-20
### Added
- make logo url configurable with app config 'logo-url', fallback to SWP one

## 3.0.0 – 2023-03-07
### Fixed
- avoid recent contact creation (from email share) if it already exists in the OX address book
- fix user menu trigger height
- fix fallback menu icon urls

## 2.0.4 – 2023-01-24
### Changed
- more meaningful ox contact api request errors

### Fixed
- more robust logo style (changes between 25.0.0 and 25.0.4)
- contact header menu entry style for NC < 25.0.4
- central menu long category names style

## 2.0.3 – 2023-01-24
### Fixed
- contact search

## 2.0.2 – 2023-01-24
### Changed
- disable cache if system config debug enabled

### Fixed
- header logo image
- compile scss to css and load it with Util::addStyle instead of importing it in scripts (fixes late style)
- fix central menu height
- fix right header menus top margin
- fix notification/contacts icons
- fix main content height

## 2.0.0 – 2023-01-17
### Changed
- compatible with NC 25-26
- make ox contact calls resistant to some api changes

### Fixed
- Adjust unified style and central menu for NC >= 25

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
