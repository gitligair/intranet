<?php

namespace App\Controller\Admin;

use App\Entity\Processus;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProcessusCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Processus::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom', 'Nom du processus'),
            TextEditorField::new('description'),
            AssociationField::new('pilotes', 'Pilote(s) processus')
                ->setFormTypeOptions([
                    'by_reference' => false, // Important pour ManyToMany
                    'multiple' => true,
                ])
                ->setCrudController(UserCrudController::class) // facultatif, pour navigation
                ->autocomplete(), // active la recherche si tu as beaucoup d’utilisateurs
            BooleanField::new('isOnline', 'En ligne'),

        ];
    }
}
