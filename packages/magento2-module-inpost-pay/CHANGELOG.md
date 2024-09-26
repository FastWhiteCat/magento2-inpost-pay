# Changelog

All notable changes to this project will be documented in this file.

## [1.0.9] - 2024-09-26

### Added

- Custom Promo Price - configuration that allows to select customer groups and product attribute that contains custom promo price
- In case of a logged in customer with group selected in configuration price from attribute will be sent
- In above case, cart total is unchanged from what Magento calculates. It is only used to display custom promo price.

### Changed

- Change method that provides browser and server data to public
- In case of no SERVER_PORT, 443 as default will be used

### Fixed

- InPost Pay Baskets merging in scenario when guest with connected cart logs in to an account with another Basket 

## [1.0.8] - 2024-09-05

### Added

- Omnibus - configuration that allows to mark rules with Omnibus flag and select which attribute contains lowest price

### Changed

- Mapping for terms and condition. It now allows to create a tree structure with sub links

### Fixed

- Zero quantity on place order from mobile App will now trigger notices and warnings 

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
