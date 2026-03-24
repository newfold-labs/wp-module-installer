---
name: wp-module-installer
title: Development
description: Lint, test, and workflow.
updated: 2025-03-18
---

# Development

## Linting

- **PHP:** `composer run lint`, `composer run fix`. Uses `phpcs.xml` and `newfold-labs/wp-php-standards`.

## Testing

- **Codeception wpunit:** `composer run test`, `composer run test-coverage`. Open `tests/_output/html/index.html` for coverage.

## i18n

- Text domain: **wp-module-installer**. Use `composer run i18n-pot`, `composer run i18n`, etc. Language files in `languages/`.

## Workflow

1. Make changes in `includes/` or `bootstrap.php`.
2. Run `composer run lint` and `composer run test` before committing.
3. When adding or changing REST routes, update [integration.md](integration.md) (and add or update **api.md** if you add an endpoints table). When cutting a release, update **docs/changelog.md**.
