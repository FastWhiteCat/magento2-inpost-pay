# Changelog

All notable changes to this project will be documented in this file.

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
