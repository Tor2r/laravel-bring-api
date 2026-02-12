<?php

use Illuminate\Support\Facades\Http;
use Tor2r\BringApi\BringApi;
use Tor2r\BringApi\Exceptions\BringApiException;
use Tor2r\BringApi\Facades\BringApi as BringApiFacade;

/*
|--------------------------------------------------------------------------
| Service Provider & Configuration
|--------------------------------------------------------------------------
*/

it('registers the BringApi singleton', function () {
    $instance = app(BringApi::class);

    expect($instance)->toBeInstanceOf(BringApi::class);
});

it('resolves the same instance from the container', function () {
    $first = app(BringApi::class);
    $second = app(BringApi::class);

    expect($first)->toBe($second);
});

it('loads config values', function () {
    expect(config('bring-api.base_url'))->toBe('https://api.bring.com/address')
        ->and(config('bring-api.default_countrycode'))->toBe('no');
});

it('resolves via the facade', function () {
    expect(BringApiFacade::getFacadeRoot())->toBeInstanceOf(BringApi::class);
});

/*
|--------------------------------------------------------------------------
| Postal Code Validation
|--------------------------------------------------------------------------
*/

it('rejects empty postal codes', function () {
    $api = app(BringApi::class);
    $api->postalCode('');
})->throws(BringApiException::class, 'Postal cannot be empty.');

/*
|--------------------------------------------------------------------------
| postalCode() - Successful Response
|--------------------------------------------------------------------------
*/

it('returns postal code data for a valid postal code', function () {
    Http::fake([
        'api.bring.com/address/api/no/postal-codes/8445' => Http::response([
            'postal_codes' => [
                [
                    'city' => 'MELBU',
                    'postal_code' => '8445',
                    'postal_code_type' => 'STREET_ADDRESSES',
                    'municipality' => 'Hadsel',
                    'municipalityId' => '1866',
                    'county' => 'Nordland',
                    'latitude' => '68.5017',
                    'longitude' => '14.8147',
                ],
            ],
        ], 200),
    ]);

    $result = app(BringApi::class)->postalCode('8445');

    expect($result)
        ->toBeArray()
        ->toHaveKey('postal_codes')
        ->and($result['postal_codes'][0]['city'])->toBe('MELBU')
        ->and($result['postal_codes'][0]['postal_code'])->toBe('8445')
        ->and($result['postal_codes'][0]['municipality'])->toBe('Hadsel');
});

it('uses the default country code when none is provided', function () {
    Http::fake([
        'api.bring.com/address/api/no/postal-codes/1555' => Http::response([
            'postal_codes' => [
                ['city' => 'SON', 'postal_code' => '1555'],
            ],
        ], 200),
    ]);

    $result = app(BringApi::class)->postalCode('1555');

    expect($result['postal_codes'][0]['city'])->toBe('SON');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/api/no/postal-codes/1555');
    });
});

it('uses a custom country code when provided', function () {
    Http::fake([
        'api.bring.com/address/api/se/postal-codes/1234' => Http::response([
            'postal_codes' => [
                ['city' => 'STOCKHOLM', 'postal_code' => '1234'],
            ],
        ], 200),
    ]);

    $result = app(BringApi::class)->postalCode('1234', 'se');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/api/se/postal-codes/1234');
    });
});

/*
|--------------------------------------------------------------------------
| postalCodeGetCity()
|--------------------------------------------------------------------------
*/

it('returns the city name for a valid postal code', function () {
    Http::fake([
        'api.bring.com/address/api/no/postal-codes/8445' => Http::response([
            'postal_codes' => [
                ['city' => 'MELBU', 'postal_code' => '8445'],
            ],
        ], 200),
    ]);

    $city = app(BringApi::class)->postalCodeGetCity('8445');

    expect($city)->toBe('MELBU');
});

it('returns null when postal code exists but has no city', function () {
    Http::fake([
        'api.bring.com/address/api/no/postal-codes/9999' => Http::response([
            'postal_codes' => [
                ['postal_code' => '9999'],
            ],
        ], 200),
    ]);

    $city = app(BringApi::class)->postalCodeGetCity('9999');

    expect($city)->toBeNull();
});

it('returns null when postal codes array is empty', function () {
    Http::fake([
        'api.bring.com/address/api/no/postal-codes/0000' => Http::response([
            'postal_codes' => [],
        ], 200),
    ]);

    $city = app(BringApi::class)->postalCodeGetCity('0000');

    expect($city)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Error Handling
|--------------------------------------------------------------------------
*/

it('throws BringApiException on 404 response', function () {
    Http::fake([
        'api.bring.com/address/api/no/postal-codes/0001' => Http::response('Not Found', 404),
    ]);

    app(BringApi::class)->postalCode('0001');
})->throws(BringApiException::class);

it('throws BringApiException on 500 response', function () {
    Http::fake([
        'api.bring.com/address/api/no/postal-codes/1234' => Http::response('Server Error', 500),
    ]);

    app(BringApi::class)->postalCode('1234');
})->throws(BringApiException::class);

it('throws BringApiException on connection error', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
    });

    app(BringApi::class)->postalCode('1234');
})->throws(BringApiException::class, 'Bring API request failed:');

/*
|--------------------------------------------------------------------------
| HTTP Client Headers
|--------------------------------------------------------------------------
*/

it('sends correct authentication headers', function () {
    config()->set('bring-api.uid', 'test@example.com');
    config()->set('bring-api.key', 'test-api-key');

    // Rebuild the singleton with new config
    app()->forgetInstance(BringApi::class);
    app()->singleton(BringApi::class, function ($app) {
        $config = $app['config']['bring-api'];

        return new BringApi(
            uid: $config['uid'] ?? '',
            key: $config['key'] ?? '',
            baseUrl: $config['base_url'] ?? 'https://api.bring.com/address',
            countryCode: $config['default_countrycode'] ?? 'no',
        );
    });

    Http::fake([
        'api.bring.com/*' => Http::response(['postal_codes' => []], 200),
    ]);

    app(BringApi::class)->postalCode('1234');

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-Mybring-API-Uid', 'test@example.com')
            && $request->hasHeader('X-Mybring-API-Key', 'test-api-key');
    });
});
