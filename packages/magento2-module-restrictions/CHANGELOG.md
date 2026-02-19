# Changelog

All notable changes to this project will be documented in this file.

## [2.7.0](https://github.com/FastWhiteCat/magento2-inpost-pay/compare/restrictions-v2.6.0...restrictions-v2.7.0) (2026-02-19)


### Features

* **repo:** initial monorepo setup with 7 InPost Pay Magento 2 modules ([bf36f6f](https://github.com/FastWhiteCat/magento2-inpost-pay/commit/bf36f6fc89fa154d4c7d96ec4b6ee4cb8025233b))

## [2.0.0] - 2025-01-17

### Fixed

- Fixed modules dependencies by moving custom logger injection from InPost_Restriction module to InPost_InPostPay module

## [1.0.8] - 2024-09-05

### Added

- Configuration that allows to set up CRON process for refreshing restricted products list
- CRON job that deletes and regenerates all products restricted by configured restriction rules

## [1.0.7] - 2024-08-08

### Added

- Configuration that separates delivery methods for restrictions allowing to restrict for example only pickup points

### Changed

- Data sent to InPost Pay API - reduced available deliveries if basket item is restricted
- Data sent to InPost Pay API - information about product allowed delivery methods

## [1.0.0]

- Initial version
