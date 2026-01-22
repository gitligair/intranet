<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherLinkService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Récupère la liste des stations accessibles pour un compte WeatherLink v2
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @return array
     */
    public function getStations(string $apiKey, string $apiSecret): array
    {
        // Timestamp en secondes
        $t = time();

        // CHAÎNE EXACTE à signer
        $stringToSign = 'api-key=' . $apiKey . '&t=' . $t;

        // Signature HMAC-SHA256 avec le secret
        $signature = hash_hmac('sha256', $stringToSign, $apiSecret);

        // URL endpoint
        $url = 'https://api.weatherlink.com/v2/stations';

        // Requête GET avec query parameters
        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'api-key' => $apiKey,
                't' => $t,
                'api-signature' => $signature,
            ],
            'http_version' => '2.0',
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        // Convertit la réponse JSON en tableau PHP
        return $response->toArray();
    }
}
