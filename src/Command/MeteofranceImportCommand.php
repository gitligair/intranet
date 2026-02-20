<?php

namespace App\Command;

use App\Services\MeteoFranceAuthService;
use App\Services\MeteoFranceService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'meteofrance:import',
    description: 'Import les donnees 24h des stations Meteo France',
)]
class MeteofranceImportCommand extends Command
{
    public function __construct(private MeteoFranceService $meteoFranceService, private MeteoFranceAuthService $meteoFranceAuthService)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1'); // optionnel

        try {
            // recuperation du token d'acces pour chaque execution
            $output->writeln('<info>Récupération du token Météo-France...</info>');
            $token = $this->meteoFranceAuthService->getAccessToken();
            $output->writeln('<info>Token OK</info>');

            $output->writeln('<info>Début de l\'import des données Météo-France...</info>');
            $data = $this->meteoFranceService->fetchAllData($token);

            $output->writeln('<info>Insertion des données en base par blocs de 500...</info>');
            foreach (array_chunk($data, 500) as $chunk) {
                $this->meteoFranceService->insertOrUpdateDonnees($chunk);
            }

            $output->writeln('<info>Import terminé avec succes</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Erreur lors de l\'import des données Météo-France : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
