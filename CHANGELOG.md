# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## 2.0.8 – 2023-03-22
### Added
- hide contacts, calendar and calendar_todo activity navigation items

## 2.0.7 – 2023-03-07
### Added
- make logo url configurable with app config 'logo-url', fallback to phoenix one

## 2.0.6 – 2023-03-07
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
