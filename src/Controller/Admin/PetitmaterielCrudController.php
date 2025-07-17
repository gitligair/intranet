<?php

namespace App\Controller\Admin;

use App\Entity\Petitmateriel;
use App\Entity\TypeMateriel;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;

class PetitmaterielCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Petitmateriel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Petit Matériel');
    }




    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Données Générales')->setIcon('fa fa-box'),

            TextField::new('nom', 'Nom')
                ->setHelp('Nom du matériel'),
            MoneyField::new('prix', 'Prix')->setCurrency('EUR'),
            DateField::new('createdAt', 'Date d\'ajout')->hideOnForm(),
            DateField::new('buyAt', 'Date d\'achat'),

            FormField::addPanel('Données Complémentaires')->setIcon('fa fa-box'),
            TextField::new('denominatif', 'Dénomination')
                ->setHelp('Dénomination du matériel'),
            TextField::new('intitule', 'Intitulé')
                ->setHelp('Intitulé du matériel'),
            SlugField::new('slug')
                ->setTargetFieldName('denominatif')
                ->setHelp('Identifiant unique du matériel')
                ->onlyOnForms(),
            TextEditorField::new('remarques', 'Remarques')
                ->setHelp('Remarques sur le matériel')
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

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Petitmateriel) {
            return;
        }

        $typeMateriel = new TypeMateriel();
        $typeMateriel = $entityManager->getRepository(TypeMateriel::class)->findOneBy(['nom' => 'Petit materiel']);

        if ($typeMateriel) {
            $entityInstance->setType($typeMateriel);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Petitmateriel) {
            return;
        }

        $typeMateriel = new TypeMateriel();
        $typeMateriel = $entityManager->getRepository(TypeMateriel::class)->findOneBy(['type' => 'Petit materiel']);

        if ($typeMateriel) {
            $entityInstance->setType($typeMateriel);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}