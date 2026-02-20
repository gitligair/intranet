<?php

namespace App\Controller\Admin;

use App\Entity\BaseDeDonnees;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class BaseDeDonneesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BaseDeDonnees::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Bases de données')
            ->setEntityLabelInSingular('Base de données')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom', 'Nom'),
            SlugField::new('slug', 'Identifiant')->setTargetFieldName('nom')->onlyOnForms(),
            AssociationField::new('localisation', 'Localisation'),
            TextField::new('user', 'User'),
            TextField::new('motdepasse', 'Mot de passe')->hideOnIndex(),
            NumberField::new('port', 'Port'),
            ChoiceField::new('typeBd', 'Type de base de données')->setChoices([
                'MySQL' => 'MySQL',
                'PostgreSQL' => 'PostgreSQL',
                'SQLite' => 'SQLite',
                'Oracle' => 'Oracle',
                'SQL Server' => 'SQL Server',
            ]),
            TextareaField::new('description', 'Description')->setFormType(CKEditorType::class),
        ];
    }
}
