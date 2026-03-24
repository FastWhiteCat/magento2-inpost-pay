# Contributing

Pull requests should be submitted to this monorepo. Split repos (`inpost/magento2-module-*`) are **read-only mirrors** — never push to them directly.

## Repository Structure

See the [README](README.md) for the full package list. Each package lives under `packages/` and all packages share the same version (set by git tag).

## Local Development Setup

Assumes you have an existing Magento 2 installation (e.g., via [Warden](https://docs.warden.dev/)).

1. Clone the monorepo:
   ```bash
   git clone <monorepo-url>
   cd magento2-inpost-pay
   ```

2. Install dev dependencies:
   ```bash
   composer install
   ```

3. Link packages into your Magento installation using path repositories. In your Magento project's `composer.json`:
   ```json
   {
     "repositories": [
       {
         "type": "path",
         "url": "/path/to/magento2-inpost-pay/packages/*"
       }
     ]
   }
   ```

4. Require the packages with `@dev` stability:
   ```bash
   composer require inpost/magento2-module-inpost-pay:@dev
   composer require inpost/magento2-module-restrictions:@dev
   # ... other packages as needed
   ```

5. Enable modules and run setup:
   ```bash
   bin/magento module:enable InPost_Restrictions InPost_InPostPay
   bin/magento setup:upgrade
   ```

## Commit Hygiene

Write clear, descriptive commit messages. We recommend (but don't enforce) [Conventional Commits](https://www.conventionalcommits.org/) format:

```
feat(inpost-pay): add support for virtual products
fix(restrictions): handle empty category list
```

## Pull Request Workflow

1. **Branch from `main`** — use a descriptive branch name (e.g., `feat/virtual-products`)
2. **Push and open a PR** — target `main`
3. **CI runs automatically** — only on packages affected by your changes
4. **Get review and merge** — squash or merge commit, both work

## Running Quality Checks Locally

```bash
# Coding standards (PHP_CodeSniffer)
composer phpcs

# Static analysis (PHPStan)
composer phpstan

# Unit tests (PHPUnit)
composer test
```

Run all three before pushing to catch issues early.

## Working Across Multiple Packages

- Use a **single PR** for cross-package changes — this keeps changes atomic and reviewable
- Inter-package dependencies use minimum version constraints (e.g., `"inpost/magento2-module-restrictions": "^2.0"`)
- When bumping a dependency between packages, include the constraint change in the same PR as the feature

## Release Process

Releases are performed by the tech owner via git tag. See [RELEASING.md](RELEASING.md) for details.
