<?php

namespace App\Controller\Admin;

use App\Entity\TaillePouce;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class TaillePouceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TaillePouce::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_SUPER_ADMIN')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Tailles en pouces')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une taille en pouces pour les ordinateurs')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la taille en pouces')
            ->setEntityLabelInSingular('Taille en pouces')
            ->setEntityLabelInPlural('Tailles en pouces')
            ->setSearchFields(['taillePouce']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            NumberField::new('taille', 'Taille en pouces'),
        ];
    }
}
