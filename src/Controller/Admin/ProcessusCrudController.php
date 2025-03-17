<?php

namespace App\Controller\Admin;

use App\Entity\Processus;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
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
            BooleanField::new('isOnline', 'En ligne'),
        ];
    }
}