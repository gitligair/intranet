<?php

namespace App\Controller\Admin;

use App\Entity\Poles;
use phpDocumentor\Reflection\Types\Boolean;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PolesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Poles::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom', 'Pôle'),
            SlugField::new('identifiant')->setTargetFieldName('nom')->onlyOnForms(),
            AssociationField::new('services', 'Service rattaché')
                ->setRequired(true) // Un Pôle doit toujours être rattaché à un Service
                ->setFormTypeOptions([
                    'choice_label' => 'nom', // Change en fonction de ton entité Service
                ]),
            AssociationField::new('responsable', 'Pilote Pôle')
                ->setFormTypeOptions([
                    'by_reference' => true, // Important pour ManyToMany
                    'multiple' => false,
                ])
                ->setCrudController(UserCrudController::class) // facultatif, pour navigation
                ->autocomplete(), // active la recherche si tu as beaucoup d’utilisateurs
            AssociationField::new('personnel', 'Present dans cette pôle :')
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
