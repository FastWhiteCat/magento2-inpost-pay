# Changelog

All notable changes to this project will be documented in this file.

## [2.4.0] - 2025-11-04

### Added

- Added support for Hyva Theme and Hyva Checkout (requires installation of InPost Pay Hyva module)
- Added new configuration for InPost Pay timed-out orders error handling
- Changed configuration for widget styles

## [2.3.1] - 2025-10-22

### Added

- Added Owebia Advanced Shipping support by adding a configuration field for mapping custom shipping method code not present in a dropdown list. 

## [2.3.0] - 2025-10-01

### Added

- Added support for downloadable and virtual products
- Added new configuration for InPost Pay Terms and Conditions (legacy configuration is still default)
- Added Magento Coupon condition that only works if an order is placed via InPost Pay Mobile App
- Added support for free orders, for example, a voucher that covers all order costs (requires InPost Mobile App upgrade)

### Fixed
- Fixed handling of backorders and disabled stock management
- Fixed error from logs for Merchants with no Smartmage_InPost logistic module installed
- Fixed: delivery_product object is no longer sent to InPost Pay API as an order response, it was unnecessary
- Fixed: after an order from a bound basket is placed via browser, the basket is deleted from InPost Pay API

## [2.2.3] - 2025-08-25

### Fixed

- Fixed deleting no longer valid InPost Pay Basket after an order has been placed in Magento Checkout from a cart bound with InPost Pay.
- Fixed a scope error in InPost Pay Admin Panel configuration validator of Shipping Methods Mapping.

## [2.2.2] - 2025-08-05

### Fixed

- Fixed the handling of free delivery set by Cart Rule with one of the conditions depending on the shipping country equals Poland.
- Fixed compatibility with newer Magento 2 Open Source v2.4.8-p1 on PHP 8.3
- Fixed compatibility with older Magento 2 Open Source v2.4.6 on PHP 8.1

## [2.2.1] - 2025-06-27

### Added

- InPost Pay Bestseller Product objects that are synchronized with InPost Pay API will now contain also Product URL
- InPost Pay Bestseller Products will now be synchronized when an attribute is saved in Admin Panel
- In the case of configuration requiring Region for Poland, InPost Pay Module will now resolve Voivodeship based on post-code first two digits provided by InPost Pay due to lack of information about a region from the incoming request. 

### Updated

- Available Payment Methods will now work the opposite way. Instead of forcing Merchant to configure his list of available payment methods, it is allowed to configure which are not available.
- Limit for InPost Pay Bestseller Products that can be synchronized with InPost Pay is no longer limited to 5. Limit only exists in InPost Pay API.

### Fixed

- Fixed sending a header with module version to InPost Pay API on each request.

## [2.2.0] - 2025-06-04

### Added

- Unique validator for delivery mapping settings
- Handling for order email in InPost Pay domain

### Fixed

- CRON Bestseller Products synchronization process should now send correct prices
- type of accepted_consents column in inpost_pay_order DB table from varchar to text to allow longer JSONs

## [2.1.1] - 2025-05-12

### Added

- More than one product images will be sent to InPost Pay as gallery if set in Magento
- Limit of 10x for configured terms and agreements handled by InPost Pay integration has been added
- Limit of 255 characters for product attributes labels and values sent to InPost Pay API
- Available in Mobile App Coupon Promotion configuration has been added
- Configuration of Bestseller Products in Magento has been added. Those products (if accepted by InPost) will be displayed to customers in their Mobile Apps.
- Quote remote initiation has been added - if Customer adds configured Bestseller Product in Mobile App the cart will be initialized and bound with InPost Pay. Previously all the carts had to be initiated in browser.
- If not configured otherwise logs will be anonymised hiding firstnames, lastnames, emails, addresses, etc.
- Configuration that allows setting up Google Analytics purchase event sending for orders created via InPost Pay Mobile App

### Updated

- Optimized shipping methods loading time
- Optimized products data loading time
- Optimized cross-sell products loading time

### Fixed

- handling of AMQP RabbitMQ integration for cart updates initiated by Magento. If configured - cart data will be sent to InPost Pay indirectly, first into RabbitMQ queue, than consumer will send data to InPost Pay API to reduce downtime of cart actions such as adding products, updating quantity, removing products
- handling of bearer token generation in asynchronous cart update mode
- handling of new order status configuration if different values has been saved for more than one website

## [2.0.7] - 2025-03-24

### Fixed

- handling of New Order Status payment method configuration for multistore with different values for each website

## [2.0.6] - 2025-03-13

### Added

- separated handling for Refund and Offline Refund Admin Panel Actions, one that refunds InPost Pay transaction, second that only creates Magento Credit Memo with no online transaction refunds

### Fixed

- fixed error when module is installed but not configured (empty: client ID, secret, merchant client ID, pos ID, Auth and API URLs)

## [2.0.5] - 2025-03-04

### Fixed

- obtaining correct Bearer Token for Transaction List for Refunds using order's Store ID instead of Default Store ID to access credentials 
- removed overriding BaseUrl for Frontend Widget with configurable value

## [2.0.4] - 2025-01-31

### Added

- configuration that allows to select if customer account should be assigned to guest quote based on InPost Pay App Account email address

### Fixed

- assigning guest cart to Magento Account based on InPost Pay Account email
- order creation process when after guest cart was assigned to an account total amount has changed resulting in InPost Pay App error

## [2.0.3] - 2025-01-17

### Fixed

- handling of order update event for legacy identification

## [2.0.2] - 2025-01-17

### Added

- sending of module version in header of requests to InPost Pay API

### Changed

- handling both order ID and order Increment ID in communication with InPost Pay API 

## [2.0.1] - 2025-01-17

### Fixed

- reference block has been changed to reference container in layout XMLs to correctly display widget in all areas

## [2.0.0] - 2025-01-17

### Removed

- Removed backend code responsible for creating, checking and deleting bindings of browser and basket between Magento and InPost Pay API
- Removed no longer handled by backend controllers 

### Added

- Added new configuration fields for authorization and display sections
- Added controller that initiates basket and returns binding API key for frontend
- Added new webapi endpoint that provides debugging logs
- Added test mode handling for production use to hide InPost Pay unless a special param is present in URL

### Changed

- Frontend code is now responsible for keeping widget bound and up to date with customer mobile App basket
- Backend controllers are no longer being constantly requested to keep InPost Widget up to date
- Communication between Magento frontend and backend has been reduced and replace with communication between frontend and InPost Pay API directly
- Payment method title from now on contains also information about chosen payment type by customer (BLIK, Card, Cash on delivery, etc.)

### Fixed

- Public Key for signature validation cache key now contains store ID to handle correctly multistore Magento instances with separated InPost Pay Client Credentials

## [1.0.12] - 2024-12-17

### Fixed

- Bundle Products will no longer throw not found exception when gathering product image URL data.

## [1.0.11] - 2024-12-05

### Fixed

- Widget will now be displayed if configured in Checkout for logged in customers.

## [1.0.10] - 2024-12-05

### Fixed

- InPost Pay Terms And Conditions Cache will now keep configured values for each store separately instead of one single cached record.
- InPost Pay Public Key for API requests Signature validation Cache will now keep Public Key versions for each store separately instead of one single cached record.

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
