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
        ];

        $this->httpClient = $httpClient;
    }

    // Fonction qui retourne les information d'une station donnée
    public function getStations(): array
    {
        // Prends n'importe laquelle des clés (chaque station a ses propres clés)
        $station = $this->stations['cnrs']; // Par exemple ici

        $timestamp = time();
        $signature = hash_hmac('sha256', 'eeiuns90ahloj5mc1sijf68yvt5khlte' . $timestamp, 'xyn8eziatyqo8rl7gwblpbdgqnv0ly3h');

        $url = 'https://api.weatherlink.com/v2/stations';

        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'api-key' => $station['apiKey'],
                't' => $timestamp,
                'api-signature' => $signature,
            ],
        ]);

        return $response->toArray();
    }



    // Méthode pour obtenir les données d'une station spécifique

    public function getStationData(string $stationName): array
    {
        if (!isset($this->stations[$stationName])) {
            throw new \InvalidArgumentException("Station inconnue: $stationName");
        }

        $station = $this->stations[$stationName];

        // Génère le timestamp actuel
        $timestamp = time();

        // Génère la signature HMAC SHA-256
        $signature = hash_hmac('sha256', $station['apiKey'] . $timestamp, $station['apiSecret']);

        $url = 'https://api.weatherlink.com/v2/current/' . $stationName;

        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'api-key' => $station['apiKey'],
                't' => $timestamp,
                'api-signature' => $signature,
            ],
        ]);

        return $response->toArray(); // Retourne le JSON sous forme de tableau PHP
    }
}