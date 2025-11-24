<?php

namespace App\Command;

use App\Services\initService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-ligair_tous',
    description: 'Add a short description for your command',
)]
class InsertionCommand extends Command
{
    private $initService;
    public function __construct(initService $initService)
    {
        parent::__construct();
        $this->initService = $initService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning('⚠️ Cette commande va VIDER la table "user" et réinitialiser depuis la liste definie dans la fonction liste_ligair');
        $confirm = $io->confirm('Souhaitez-vous continuer ?', false);

        if (!$confirm) {
            $io->info('Opération annulée.');
            return Command::SUCCESS;
        }

        try {
            $this->initService->insert_user($this->initService->liste_ligair());
            $io->success('✅ La table "user" a été vidée avec succès.');
        } catch (\Exception $e) {
            $io->error('❌ Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
