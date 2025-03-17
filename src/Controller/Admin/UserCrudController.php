<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setDateFormat('dd/MM/yyyy')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['id' => 'DESC'])
            // ...
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('prenom'),
            TextField::new('nom'),
            EmailField::new('email'),
            SlugField::new('identifiant')->setTargetFieldName(['prenom', 'nom'])->onlyOnForms(),
            TextField::new('identifiant')->hideOnForm(),
            TextField::new('password', 'mot de passe')->setFormattedValue(PasswordType::class)->onlyOnForms(),
            TextField::new('password_', "Re-tapez le mot de passe")->setFormattedValue(PasswordType::class)->onlyOnForms(),
        ];
    }
}