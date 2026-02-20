<?php

namespace App\Services;

use Doctrine\DBAL\Connection;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeteoFranceAuthService
{
    private ?string $accessToken = null;
    private ?\DateTime $expiresAt = null;

    public function __construct(private HttpClientInterface $httpClient, private Connection $connection)
    {
        $this->httpClient = $httpClient;
        $this->connection = $connection;
    }

    public function getAccessToken(): string
    {
        $response = $this->httpClient->request(
            'POST',
            'https://portail-api.meteofrance.fr/token',
            [
                'headers' => [
                    'Authorization' => 'Basic ' . $_ENV['METEO_FRANCE_TOKEN'],
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'client_credentials',
                ],
            ]
        );

        $data = $response->toArray(false);

        if (!isset($data['access_token'])) {
            throw new \RuntimeException(
                'Token MF non retourné : ' . json_encode($data)
            );
        }

        return $data['access_token'];
    }
}
