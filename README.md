# Laravel Bring API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tor2r/laravel-bring-api.svg?style=flat-square)](https://packagist.org/packages/tor2r/laravel-bring-api)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/tor2r/laravel-bring-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/tor2r/laravel-bring-api/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/tor2r/laravel-bring-api/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/tor2r/laravel-bring-api/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/tor2r/laravel-bring-api.svg?style=flat-square)](https://packagist.org/packages/tor2r/laravel-bring-api)

A Laravel package for interacting with the [Bring API](https://developer.bring.com/api/). Fetch postal code information and addresses for Norwegian
locations.

Supported Countries

- NO - Norway (default)
- DK - Denmark
- SE - Sweden
- FI - Finland
- NL - Netherlands
- DE - Germany
- US - United States
- BE - Belgium
- FO - Faroe Islands
- GL - Greenland
- IS - Iceland
- SJ - Svalbard and Jan Mayen

## Installation

You can install the package via composer:

```bash
composer require tor2r/laravel-bring-api
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="bring-api-config"
```

## Configuration

Add the following environment variables to your `.env` file:

```env
BRING_API_UID=your-mybring-email@example.com
BRING_API_KEY=your-api-key
```

You can get your API credentials by registering at [Mybring](https://www.mybring.com/) and generating an API key in your account settings.

The published config file (`config/bring-api.php`) contains:

```php
return [
    'uid' => env('BRING_API_UID'),
    'key' => env('BRING_API_KEY'),
    'base_url' => env('BRING_API_BASE_URL', 'https://api.bring.com/address'),
    'default_countrycode' => env('BRING_API_DEFAULT_COUNTRYCODE', 'no'),
];
```

## Usage

### Using the Facade

#### Get city name for a postal code

```php
use Tor2r\BringApi\Facades\BringApi;

$city = BringApi::postalCodeGetCity('8445'); // Default $countryCode = 'no'
// Returns: "Melbu"
```

#### Get full postal code information

```php
use Tor2r\BringApi\Facades\BringApi;

$data = BringApi::postalCode('1555');
// Returns an array with postal code details:
// [
//     'postal_codes' => [
//         [
//             'city' => 'Son',
//             'postal_code' => '1555',
//             'postal_code_type' => 'STREET_ADDRESSES',
//             'municipality' => 'Vestby',
//             'municipalityId' => '3019',
//             'county' => 'Akershus',
//             'latitude' => '59.5237',
//             'longitude' => '10.6862',
//         ]
//     ]
// ]
```

### Using Dependency Injection

```php
use Tor2r\BringApi\BringApi;

class MyController
{
    public function show(BringApi $bringApi, string $postalCode)
    {
        $city = $bringApi->postalCodeGetCity($postalCode);

        return response()->json(['city' => $city]);
    }
}
```

### Error Handling

The package throws `Tor2r\BringApi\Exceptions\BringApiException` when something goes wrong. Norwegian postal codes must be exactly 4 digits.

```php
use Tor2r\BringApi\Facades\BringApi;
use Tor2r\BringApi\Exceptions\BringApiException;

try {
    $city = BringApi::postalCodeGetCity('0000'); // Non-existent postal code
} catch (BringApiException $e) {
    // API error response
}
```

### Available Methods

| Method                                                              | Description                              | Returns   |
|---------------------------------------------------------------------|------------------------------------------|-----------|
| `postalCode(string $postalCode, string $countryCode = 'no')`        | Get full postal code data from Bring API | `array`   |
| `postalCodeGetCity(string $postalCode, string $countryCode = 'no')` | Get only the city name for a postal code | `?string` |

### Response Fields

The `postalCode()` method returns an array containing a `postal_codes` key with the following fields per entry:

| Field              | Type   | Description                                                         |
|--------------------|--------|---------------------------------------------------------------------|
| `postal_code`      | string | The postal code                                                     |
| `city`             | string | City name                                                           |
| `municipality`     | string | Municipality name                                                   |
| `municipalityId`   | string | Municipality ID                                                     |
| `county`           | string | County name                                                         |
| `postal_code_type` | string | One of: `STREET_ADDRESSES`, `PO_BOX`, `COMBINED`, `SPECIAL_SERVICE` |
| `latitude`         | string | Latitude (WGS84)                                                    |
| `longitude`        | string | Longitude (WGS84)                                                   |

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Tor L](https://github.com/Tor2r)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
