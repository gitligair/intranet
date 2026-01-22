<?php

namespace App\Controller\Admin;

use App\Entity\Os;
use App\Entity\User;
use App\Entity\Ecran;
use App\Entity\Poles;
use App\Entity\Bureau;
use App\Entity\Materiel;
use App\Entity\Services;
use App\Entity\Categorie;
use App\Entity\Processus;
use App\Entity\Accessoire;
use App\Entity\BaseScript;
use App\Entity\Ordinateur;
use App\Entity\TaillePouce;
use App\Entity\CotechVacarm;
use App\Entity\TypeMateriel;
use App\Entity\Petitmateriel;
use App\Entity\SousCategorie;
use App\Entity\CotechVacarmMateriel;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
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
            ->setTitle('<h5 class="text-white fst-italic ">Intranet LIG\'AIR</h5>')

            ->setFaviconPath('images/logos/ligair.svg')

        ;
    }



    public function configureAssets(): Assets
    {
        return Assets::new()
            // ->addWebpackEncoreEntry('app') // ← crucial

            ->addCssFile('css/easyadmin-custom.css')
            ->addCssFile('css/ea-notification.css') // si tu as aussi le style
            ->addJsFile('js/planning/ea-notification.js');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home'),
            MenuItem::section('Organisation'),
            MenuItem::linkToRoute('Formulaire Agile', 'fa fa-user', 'forumlaire_agile'),
            // MenuItem::linkToRoute('Planning hebdomadaire', 'fa fa-calendar-week', 'admin_planning'),
            MenuItem::linkToRoute('Planning par personne', 'fa fa-user', 'admin_planning_grille'),
            // MenuItem::linkToRoute('📊 Planning par pôle', 'fa fa-building', 'admin_planning_recap_poles'),


            MenuItem::section('Systeme qualité'),
            MenuItem::linkToCrud('Processus', 'fa-solid fa-microchip', Processus::class),
            MenuItem::linkToCrud('Services', 'fa fa-file-text', Services::class),
            MenuItem::linkToCrud('Pôles Service', 'fa fa-file-text', Poles::class),

            MenuItem::section('Utilisateurs..'),
            MenuItem::linkToCrud('LIGAIR tous', 'fa fa-user', User::class),

            MenuItem::section('Informatique'),
            MenuItem::linkToCrud('Bureaux', 'fa-solid fa-house-laptop', Bureau::class),
            MenuItem::subMenu('Configuration', 'fa-solid fa-tools')->setSubItems([
                MenuItem::linkToCrud('Types de matériel', 'fa-solid fa-box', TypeMateriel::class),
            ]),
            MenuItem::linkToCrud('Liste des scripts', 'fa-solid fa-house-laptop', BaseScript::class),

            MenuItem::section('Matériels informatiques'),
            MenuItem::subMenu('Désignations', 'fa-brands fa-typo3')->setSubItems([
                MenuItem::linkToCrud('Catégories', 'fa-solid fa-box', Categorie::class),
                MenuItem::linkToCrud('Sous-catégories', 'fa-solid fa-box', SousCategorie::class),
                MenuItem::linkToCrud('Tailles en pouces', 'fa-solid fa-box', TaillePouce::class),
                MenuItem::linkToCrud('Systemes d\'exploitation', 'fa-solid fa-box', Os::class),
            ]),
            MenuItem::linkToCrud('Tous matériels', 'fa-solid fa-box', Materiel::class),
            MenuItem::linkToCrud('Ecrans', 'fa-solid fa-window-maximize', Ecran::class),
            MenuItem::linkToCrud('Ordinateurs', 'fa-solid fa-desktop', Ordinateur::class),
            MenuItem::linkToCrud('Autres materiels', 'fa-brands fa-usb', Petitmateriel::class),
            MenuItem::linkToCrud('Accessoires', 'fa-brands fa-usb', Accessoire::class),

            MenuItem::section('Coteech VACARM'),
            MenuItem::linkToCrud('COTECH VACARM', 'fa-solid fa-gears', CotechVacarm::class),
            MenuItem::linkToCrud('Materiel', 'fa-solid fa-gears', CotechVacarmMateriel::class),

        ];
    }
}
