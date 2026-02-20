<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApimeteocentreService
{

    private array $stations;
    private HttpClientInterface $httpClient;

    public function __construct(
        string $apiKey_cnrs,
        string $apiSecret_cnrs,
        string $apiKey_droue,
        string $apiSecret_droue,
        string $apiKey_bazoches,
        string $apiSecret_bazoches,
        string $apiKey_ligair,
        string $apiSecret_ligair,
        HttpClientInterface $httpClient
    ) {
        $this->stations = [
            'cnrs' => [
                'apiKey' => $apiKey_cnrs,
                'apiSecret' => $apiSecret_cnrs,

            ],
            'droue' => [
                'apiKey' => $apiKey_droue,
                'apiSecret' => $apiSecret_droue,

            ],
            'bazoches' => [
                'apiKey' => $apiKey_bazoches,
                'apiSecret' => $apiSecret_bazoches,

            ],
            'ligair' => [
                'apiKey' => $apiKey_ligair,
                'apiSecret' => $apiSecret_ligair,

            ],
        ];

        $this->httpClient = $httpClient;
    }

    // Fonction qui retourne les information d'une station donnée
    public function getStations(): array
    {
        $station = $this->stations['ligair'];
        // timestamp UTC actuel
        $t = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp();

        // paramètres API
        $queryParams = [
            'api-key' => $station['apiKey'], // TA clé API
            't' => $t,
        ];

        // tri alphabétique OBLIGATOIRE
        ksort($queryParams);

        // génération query string RFC3986
        $queryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

        // CHAÎNE à signer (exactement 4 lignes)
        $stringToSign = implode("\n", [
            'GET',                        // méthode HTTP
            'api.weatherlink.com',        // host
            '/v2/stations',               // endpoint path
            $queryString                  // query string triée
        ]);

        // signature HMAC SHA256 avec LE SECRET de ton compte
        $signature = hash_hmac('sha256', $stringToSign, $station['apiSecret']);

        // URL complète
        $url = 'https://api.weatherlink.com/v2/stations';

        // appel HTTP GET
        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'api-key'       => $station['apiKey'],
                't'             => $t,
                'api-signature' => $signature,
            ],
        ]);

        // décoder JSON
        return $response->toArray();
    }




    // Méthode pour obtenir les données d'une station spécifique

    public function getStationData(string $stationKey, string $stationId): array
    {
        if (!isset($this->stations[$stationKey])) {
            throw new \InvalidArgumentException("Station inconnue: $stationKey");
        }

        $station = $this->stations[$stationKey];
        $timestamp = time();

        $base = $station['apiSecret'] .
            'api-key' . $station['apiKey'] .
            't' . $timestamp;

        $signature = hash('sha256', $base);

        $url = 'https://api.weatherlink.com/v2/current/' . $stationId;

        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'api-key' => $station['apiKey'],
                't' => $timestamp,
                'api-signature' => $signature,
            ],
        ]);

        return $response->toArray();
    }
}
