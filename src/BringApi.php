<?php

namespace Tor2r\BringApi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Tor2r\BringApi\Exceptions\BringApiException;

class BringApi
{
    protected string $uid;

    protected string $key;

    protected string $baseUrl;

    protected string $countryCode;

    public function __construct(string $uid, string $key, string $baseUrl, string $countryCode = 'no')
    {
        $this->uid = $uid;
        $this->key = $key;
        $this->baseUrl = $baseUrl;
        $this->countryCode = $countryCode;
    }

    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'X-Mybring-API-Uid' => $this->uid,
            'X-Mybring-API-Key' => $this->key,
        ])->acceptJson()->baseUrl($this->baseUrl);
    }

    /**
     * Get postal code information from the Bring API.
     *
     * @return array<string, mixed>
     *
     * @throws BringApiException
     */
    public function postalCode(string $postalCode, ?string $countryCode = null): array
    {
        if (empty($postalCode)) {
            throw new BringApiException('Postal cannot be empty.');
        }

        $country = $countryCode ?? $this->countryCode;

        try {
            $response = $this->client()->get("/api/{$country}/postal-codes/{$postalCode}");

            if ($response->failed()) {
                throw new BringApiException(
                    "Bring API request failed with status {$response->status()}: {$response->body()}"
                );
            }

            return $response->json() ?? [];
        } catch (BringApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new BringApiException("Bring API request failed: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Get the city name for a given postal code.
     *
     * @throws BringApiException
     */
    public function postalCodeGetCity(string $postalCode, ?string $countryCode = null): ?string
    {
        $data = $this->postalCode($postalCode, $countryCode);

        return $data['postal_codes'][0]['city'] ?? null;
    }
}
