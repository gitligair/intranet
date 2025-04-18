<?php

namespace App\Controller\Admin;

use App\Entity\Ecran;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EcranCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ecran::class;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('js/isStockToggle.js');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Données Générales')->setIcon('fa fa-box'),
            AssociationField::new('type', 'Type de matériel'),
            TextField::new('nom', 'Nom'),
            MoneyField::new('prix', 'Prix')->setCurrency('EUR'),
            DateField::new('createdAt', 'Date de création')->hideOnForm(),
            DateField::new('buyAt', 'Date d\'achat'),



            FormField::addPanel('Détails Écran')->setIcon('fa fa-desktop'),
            TextField::new('marque', 'Marque'),
            TextField::new('numSerie', 'Numéro de série'),
            TextField::new('taille', 'Taille'),
            ArrayField::new('connecteurs', 'Connecteurs')
                ->setHelp('Exemple : ["HDMI", "VGA", "DP"]'),

            FormField::addPanel('Affectation')->setIcon('fa fa-user'),
            BooleanField::new('isStock', 'En stock'),
            AssociationField::new('localisation', 'Bureau')
                ->setFormTypeOption('disabled', false)
                ->setHelp('Bureau où se trouve le matériel'),
            AssociationField::new('utilisateur', 'Utilisateur')
                ->setFormTypeOption('disabled', false)
                ->setHelp('Utilisateur à qui le matériel est affecté'),
        ];
    }
}