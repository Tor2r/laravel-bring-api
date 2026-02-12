<?php

// config for Tor2r/BringApi
return [
    'uid' => env('BRING_API_UID'),
    'key' => env('BRING_API_KEY'),
    'base_url' => env('BRING_API_BASE_URL', 'https://api.bring.com/address'),
    'default_countrycode' => env('BRING_API_DEFAULT_COUNTRYCODE', 'no'),
];
