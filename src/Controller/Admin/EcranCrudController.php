<?php

namespace App\Controller\Admin;

use App\Entity\Ecran;
use App\Entity\TypeMateriel;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des écrans')
            ->setPageTitle('new', 'Ajouter un écran')
            ->setPageTitle('edit', fn(Ecran $ecran) => sprintf('Modifier les informations concernant l\'écran <b>%s</b>', $ecran->getNom()))
            ->setEntityLabelInSingular('Écran')
            ->setEntityLabelInPlural('Écrans')
            ->setSearchFields(['nom', 'marque', 'numSerie'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }



    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Données Générales')->setIcon('fa fa-box'),

            TextField::new('nom', 'Nom'),
            MoneyField::new('prix', 'Prix')->setCurrency('EUR'),
            DateField::new('createdAt', 'Date d\'ajout')->hideOnForm()->hideOnIndex(),
            DateField::new('buyAt', 'Date d\'achat'),


            FormField::addPanel('Détails Écran')->setIcon('fa fa-desktop'),
            TextField::new('marque', 'Marque'),
            TextField::new('numSerie', 'Numéro de série'),
            NumberField::new('taille', 'Taille')
                ->setHelp('Taille de l\'écran en pouces')
                ->setColumns(6)
                ->hideOnIndex(),
            ArrayField::new('connecteurs', 'Connecteurs')
                ->setHelp('Exemple : ["HDMI", "VGA", "DP"]')
                ->hideOnIndex(),


            FormField::addPanel('Affectation')->setIcon('fa fa-user'),
            BooleanField::new('isStock', 'En stock'),
            AssociationField::new('localisation', 'Bureau')
                ->setCssClass('enStock')
                ->setFormTypeOption('disabled', false)
                ->setHelp('Bureau où se trouve le matériel')
                ->hideOnIndex(),
            AssociationField::new('utilisateur', 'Utilisateur')
                ->setCssClass('enStock')
                ->setFormTypeOption('disabled', false)
                ->setHelp('Utilisateur à qui le matériel est affecté')
                ->hideOnIndex(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Ecran) {
            return;
        }

        $typeMateriel = new TypeMateriel();
        $typeMateriel = $entityManager->getRepository(TypeMateriel::class)->findOneBy(['nom' => 'Ecran']);

        if ($typeMateriel) {
            $entityInstance->setType($typeMateriel);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Ecran) {
            return;
        }

        $typeMateriel = new TypeMateriel();
        $typeMateriel = $entityManager->getRepository(TypeMateriel::class)->findOneBy(['nom' => 'Ecran']);

        if ($typeMateriel) {
            $entityInstance->setType($typeMateriel);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}