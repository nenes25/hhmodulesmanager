# Hh Module manager

[![GitHub stars](https://img.shields.io/github/stars/nenes25/hhmodulesmanager)](https://github.com/nenes25/hhmodulesmanager/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/nenes25/hhmodulesmanager)](https://github.com/nenes25/hhmodulesmanager/network)
[![Github All Releases](https://img.shields.io/github/downloads/nenes25/hhmodulesmanager/total.svg)]()
[![PHP Tests](https://github.com/nenes25/hhmodulesmanager/actions/workflows/php.yml/badge.svg)](https://github.com/nenes25/hhmodulesmanager/actions/workflows/php.yml)

Module Prestashop de gestion des mises à jour des modules et configuration via la CLI 

You can find more information about this module in the following articles (in French) :

- https://www.h-hennes.fr/blog/2023/11/06/prestashop-comment-limiter-les-interactions-manuelles-avec-le-deploiement-continu/
- https://www.h-hennes.fr/blog/2023/11/12/prestashop-hhmodule-manager-fonctionnement-technique-et-extension/


Compatibility
---

| Prestashop Version | Compatible |
|--------------------| ---------|
| 1.7.8.x | :heavy_check_mark: |
| 8.x | :heavy_check_mark: |
| 9.0 | In progress |



| Php Version | Compatible                   |
|-------------|------------------------------|
| Under 7.4   | :x:           |
| 7.4         | :heavy_check_mark: (Tested in CI)           |
| 8.1         | :heavy_check_mark: (Tested in CI) |
| 8.2         | :heavy_check_mark: (Tested in CI) |
| 8.3         | :heavy_check_mark: (Tested in CI) |
| 8.4         | :heavy_check_mark: (Linter only) |

## Development

### Running Tests

This module includes a comprehensive test suite with unit and integration tests.

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run unit tests only
composer test-unit

# Run integration tests only (requires PrestaShop installation)
composer test-integration
```

**Test Coverage:**
- ✅ 29 unit tests (Converters: Configuration, Module, Translation)
- ✅ 11 integration tests (Configuration Upgrader)
- ✅ Automatic CI/CD on PHP 7.4, 8.1, 8.2, 8.3

See [tests/README.md](tests/README.md) for more details on running and writing tests.
