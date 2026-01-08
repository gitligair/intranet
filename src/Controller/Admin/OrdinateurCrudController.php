<?php

namespace App\Controller\Admin;

use App\Entity\Ordinateur;
use App\Entity\TypeMateriel;
use Gedmo\Mapping\Annotation\Slug;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OrdinateurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ordinateur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Ordinateurs')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un ordinateur')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l\'ordinateur')
            ->setEntityLabelInSingular('Ordinateur')
            ->setEntityLabelInPlural('Ordinateurs')
            ->setSearchFields(['modele', 'categorie', 'sousCategorie', 'processeur'])
            ->setDefaultSort(['createdAt' => 'DESC']);
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
            AssociationField::new('types', 'Catégorie')
                ->setColumns(4)
                ->setHelp('Exemple : Pc ou serveur Rack'),
            AssociationField::new('sousCatPoste', 'Sous-catégorie')
                ->setColumns(4)
                ->setHelp('Exemple : Fixe ou Portable')
                ->hideOnIndex(),
            AssociationField::new('taillePouce', 'Taille en pouces')
                ->setColumns(4)
                ->setHelp('Exemple : 15 pouces')
                ->hideOnIndex(),
            TextField::new('nom', 'Nom')
                ->setHelp('Nom de l\'ordinateur'),
            MoneyField::new('prix', 'Prix')->setCurrency('EUR')->hideOnIndex(),
            DateField::new('createdAt', 'Date d\'ajout')->hideOnForm()->hideOnIndex(),
            DateField::new('buyAt', 'Date d\'achat')->hideOnIndex(),


            FormField::addPanel('Détails Ordinateur')->setIcon('fa fa-desktop'),
            TextField::new('modele', 'Modele')
                ->setColumns(12)
                ->setHelp('Exemple : Dell XPS 13')
                ->hideOnIndex(),
            TextField::new('identifiant', 'Numéro de série')
                ->setHelp('Identifiant unique de l\'ordinateur')
                ->setColumns(6)
                ->hideOnIndex(),
            SlugField::new('slug', 'Identifiant')
                ->setTargetFieldName('identifiant')
                ->setHelp('Identifiant unique de l\'ordinateur')
                ->onlyOnForms()
                ->setColumns(6),
            TextField::new('processeur', 'Processeur')
                ->setHelp('Exemple : Intel Core i7-10700K')
                ->setColumns(6),
            AssociationField::new('systemeExploitation', 'Système d\'exploitation')
                ->setColumns(6),
            NumberField::new('ram', 'RAM (en Go)')
                ->setHelp('Exemple : 16')
                ->setColumns(6),
            NumberField::new('stockage', 'Stockage (en Go)')
                ->setHelp('Exemple : 512')
                ->setColumns(6),
            ArrayField::new('logiciels', 'logiciels')
                ->setHelp('Exemple : ["Teams","office"]')
                ->setColumns(6)
                ->hideOnIndex(),

            FormField::addPanel('Affectation')->setIcon('fa fa-user'),
            BooleanField::new('isStock', 'En stock')->hideOnIndex(),
            AssociationField::new('localisation', 'Bureau')
                ->setCssClass('enStock')
                ->setFormTypeOption('disabled', false)
                ->setHelp('Bureau où se trouve le matériel')
                ->hideOnIndex(),
            AssociationField::new('utilisateur', 'Utilisateur')
                ->setCssClass('enStock')
                ->setFormTypeOption('disabled', false)
                ->setHelp('Utilisateur à qui le matériel est affecté'),

        ];
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, 'detail');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Ordinateur) {
            return;
        }
        $typeMateriel = new TypeMateriel();
        $typeMateriel = $entityManager->getRepository(TypeMateriel::class)->findOneBy(['nom' => 'Ordinateur']);

        if ($typeMateriel) {
            $entityInstance->setType($typeMateriel);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Ordinateur) {
            return;
        }
        $typeMateriel = new TypeMateriel();
        $typeMateriel = $entityManager->getRepository(TypeMateriel::class)->findOneBy(['nom' => 'Ordinateur']);

        if ($typeMateriel) {
            $entityInstance->setType($typeMateriel);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}
