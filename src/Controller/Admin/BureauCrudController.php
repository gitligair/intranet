<?php

namespace App\Controller\Admin;

use App\Entity\Bureau;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BureauCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Bureau::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom', 'Nom du bureau'),
            AssociationField::new('occupant', 'Occupant')
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('multiple', true)
                ->setHelp('Utilisateur à qui le matériel est affecté'),
            TextEditorField::new('description'),
        ];
    }
}