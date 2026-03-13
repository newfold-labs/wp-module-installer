# Getting started

## Prerequisites

- **PHP** 7.3+.
- **Composer.** The module requires `newfold-labs/wp-module-pls`.

## Install

```bash
composer install
```

## Run tests

```bash
composer run test
composer run test-coverage
```

## Lint and i18n

```bash
composer run lint
composer run fix
composer run i18n
```

Text domain: **wp-module-installer**. See [development.md](development.md).

## Using in a host plugin

1. Depend on `newfold-labs/wp-module-installer` (and ensure wp-module-pls is present).
2. The module registers with the loader on `plugins_loaded`. No further configuration required; the REST API and CLI are available when the module is active.

See [integration.md](integration.md).
