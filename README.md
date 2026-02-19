# InPost Pay for Magento 2

Monorepo containing all InPost Pay Magento 2 modules.

## Packages

| Package | Composer name | Description |
|---|---|---|
| [Restrictions](packages/magento2-module-restrictions) | `inpost/magento2-module-restrictions` | Product restrictions for InPost Pay |
| [InPost Pay](packages/magento2-module-inpost-pay) | `inpost/magento2-module-inpost-pay` | Core InPost Pay integration |
| [InPost Pay GraphQL](packages/magento2-module-inpost-pay-graph-ql) | `inpost/magento2-module-inpost-pay-graph-ql` | GraphQL endpoints |
| [InPost Pay Commerce](packages/magento2-module-inpost-pay-commerce) | `inpost/magento2-module-inpost-pay-commerce` | Adobe Commerce support |
| [Restrictions Commerce](packages/magento2-module-restrictions-commerce) | `inpost/magento2-module-restrictions-commerce` | Adobe Commerce restrictions support |
| [InPost Pay Hyva Checkout](packages/magento2-module-inpost-pay-hyva-checkout) | `inpost/magento2-module-inpost-pay-hyva-checkout` | Hyva Checkout integration |
| [InPost Pay Hyva Theme](packages/magento2-module-inpost-pay-hyva-theme) | `inpost/magento2-module-inpost-pay-hyva-theme` | Hyva Theme integration |

## Installation

Install individual packages via Composer:

```bash
composer require inpost/magento2-module-inpost-pay
bin/magento module:enable InPost_Restrictions InPost_InPostPay
bin/magento setup:upgrade
```

## Development

```bash
# Install dev dependencies
composer install

# Run coding standards check
composer phpcs

# Run static analysis
composer phpstan

# Run tests
composer test
```

## Contributing

Submit pull requests to this monorepo. Split repos (`inpost/magento2-module-*`) are read-only mirrors.

## License

See individual package LICENSE files.
