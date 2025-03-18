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
            BooleanField::new('isOnline', 'En ligne'),
        ];
    }
}