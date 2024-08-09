# Changelog

All notable changes to this project will be documented in this file.

## [1.0.7] - 2024-08-08

### Changed

- Configuration - Hardcoded list of available payment methods has been replaced with API loaded list
- Data sent to InPost Pay API - basket - coupon codes notifications have been reorganised
- Data sent to InPost Pay API - order details - tracking numbers are now added every time tracking object is saved

### Fixed

- Data sent to InPost Pay API - basket - fixed handling for delivery methods free delivery threshold configuration
- Data sent to InPost Pay API - order creation - fixed handling no address details as separated street, flat and number
- Data sent to InPost Pay API - order details - fixed configurable product's child simple product actual price
- Data sent to InPost Pay API - order details - fixed product image URLs for simple and configurable variants

## [1.0.6] - 2024-07-19

### Added

- Widget display toggle switch configuration per store
- Widget Min Height parameter configuration
- Configuration that allows to set which product image roles will be displayed in InPost Pay Mobile app

### Fixed

- Mass Action cancel status sending to InPost Pay API

## [1.0.5] - 2024-07-05

### Added

- Long Polling configuration for frontend widget

## [1.0.4] - 2024-06-27

### Added

- Third color version option in configuration

## [1.0.3] - 2024-06-26

### Added

- Configuration and priority for firstname and lastname data source for InPost Pay Order - Customer or Address.
- Handling for products with disabled stock management

## [1.0.2] - 2024-06-19

### Fixed

- Prevented text attributes containing no non-HTML code after cleaning from sending as empty value to InPost Pay API
- Removed additional tax amount and net price validation on order create request. Only final gross price is validated.

## [1.0.0]

- Initial version
