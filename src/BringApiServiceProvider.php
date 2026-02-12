<?php

namespace Tor2r\BringApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BringApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-bring-api')
            ->hasConfigFile('bring-api');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(BringApi::class, function ($app) {
            $config = $app['config']['bring-api'];

            return new BringApi(
                uid: $config['uid'] ?? '',
                key: $config['key'] ?? '',
                baseUrl: $config['base_url'] ?? 'https://api.bring.com/address',
                countryCode: $config['default_countrycode'] ?? 'no',
            );
        });
    }
}
