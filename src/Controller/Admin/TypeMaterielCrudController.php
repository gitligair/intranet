<?php

namespace App\Controller\Admin;

use App\Entity\TypeMateriel;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TypeMaterielCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeMateriel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Types de matériel')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un type de matériel')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le type de matériel')
            ->setEntityLabelInSingular('Type de matériel')
            ->setEntityLabelInPlural('Types de matériel')
            ->setSearchFields(['nom'])
            ->setDefaultSort(['nom' => 'ASC']);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom', 'Nom')
                ->setHelp('Nom du type de matériel'),
            SlugField::new('identifiant', 'Identifiant')->hideOnIndex()
                ->setTargetFieldName('nom')
                ->setHelp('Identifiant du type de matériel'),
            DateTimeField::new('createdAt', 'Date de création')
                ->setHelp('Date de création du type de matériel')
                ->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Date de mise à jour')
                ->setHelp('Date de mise à jour du type de matériel')
                ->onlyOnDetail(),
        ];
    }
}