# Changelog

All notable changes to `laravel-bring-api` will be documented in this file.

## 0.1.1 - 2026-02-12

### Added

- Configurable default country code via `default_countrycode` config option
- Comprehensive test suite (19 tests covering validation, API responses, error handling, service provider, and facade)

### Fixed

- Removed leftover `dump()` debug statement
- Country code is now injected via constructor instead of calling `config()` directly

## 0.1.0 - 2026-02-11

### Added

- Bring API authentication with `X-Mybring-API-Uid` and `X-Mybring-API-Key` headers
- `postalCode()` method to fetch full postal code information from Bring API
- `postalCodeGetCity()` method to get the city name for a Norwegian postal code
- Custom `BringApiException` for error handling
- Configuration file with `uid`, `key`, `base_url`, and `default_countrycode` settings
- Facade support via `BringApi::` syntax
