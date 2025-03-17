<?php

namespace App\Controller\Admin;

use App\Entity\Services;
use App\Form\PolesType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use phpDocumentor\Reflection\Types\Boolean;

class ServicesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Services::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom', 'Nom du service'),
            SlugField::new('identifiant')->setTargetFieldName('nom')->onlyOnForms(),
            AssociationField::new('responsable', 'Responsable du service'),
            CollectionField::new('poles_service', 'Poles')
                ->setEntryType(PolesType::class) // Utilisation du formulaire PoleType
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                ])
                ->renderExpanded(),
            AssociationField::new('processus', 'Processus'),
            BooleanField::new('isOnline', 'En ligne'),
        ];
    }
}