<?php

namespace App\Controller\Admin;

use App\Entity\Poles;
use App\Entity\Processus;
use App\Entity\User;
use App\Entity\Services;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdministrationController extends AbstractDashboardController
{
    public function index(): Response
    {

        return $this->render('admin/admin.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Intranet LIG\'AIR')
            ->setFaviconPath('images/logos/ligair.svg')

        ;
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Systeme qualité'),
            MenuItem::linkToCrud('Processus', 'fa-solid fa-microchip', Processus::class),
            MenuItem::linkToCrud('Services', 'fa fa-file-text', Services::class),
            MenuItem::linkToCrud('Pôles Service', 'fa fa-file-text', Poles::class),

            MenuItem::section('Utilisateurs..'),
            MenuItem::linkToCrud('LIGAIR tous', 'fa fa-user', User::class),
        ];
    }
}