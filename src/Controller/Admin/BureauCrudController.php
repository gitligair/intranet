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

            // affichage sur index
            AssociationField::new('occupant', 'Occupants')
                ->onlyOnIndex()
                ->formatValue(function ($value, $bureau) {
                    $users = $bureau->getOccupant(); // Collection<User>
                    if ($users->isEmpty()) return '';

                    return implode('<br/>', $users->map(fn($u) => (string) $u)->toArray());
                }),

            // champ relation pour les formulaires
            AssociationField::new('occupant', 'Occupants')
                ->onlyOnForms()
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('multiple', true)
                ->setHelp('Utilisateurs affectés à ce bureau'),

            TextEditorField::new('description'),
        ];
    }
}
