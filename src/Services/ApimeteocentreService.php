<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApimeteocentreService
{

    private array $stations;
    private HttpClientInterface $httpClient;

    public function __construct(
        private HttpClientInterface $http,
        private string $apiKey_ligair,
        private string $apiSecret_ligair,
    ) {}

    public function listStations(): array
    {
        $response = $this->http->request('GET', 'https://api.weatherlink.com/v2/stations', [
            'query' => [
                'api-key' => $this->apiKey_ligair,
            ],
            'headers' => [
                'X-Api-Secret' => $this->apiSecret_ligair,
                'Accept' => 'application/json',
            ],
        ]);

        // Lève une exception Symfony si 4xx/5xx
        $data = $response->toArray(false);

        return $data;
    }

    // Fonction qui retourne les données d'une station donnée
    public function getStationData($stationId): array
    {
        $debut = new \DateTime('2026-02-04 00:00:00')->getTimestamp();
        $fin = new \DateTime('2026-02-05 00:00:00')->getTimestamp();


        $response = $this->http->request('GET', "https://api.weatherlink.com/v2/historic/{$stationId}", [
            'query' => [
                'api-key' => $this->apiKey_ligair,
                'start-timestamp' => $debut,
                'end-timestamp' => $fin,
            ],
            'headers' => [
                'X-Api-Secret' => $this->apiSecret_ligair,
                'Accept' => 'application/json',
            ],
        ]);
        $data = $response->toArray(false);
        return $data;
    }
}
