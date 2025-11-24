<?php

namespace App\Controller\Admin;

use App\Form\PolesType;
use App\Entity\Services;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ServicesCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Services::class;
    }

    // public function configureActions(Actions $actions): Actions
    // {
    //     return $actions
    //         // ...
    //         ->add(Crud::PAGE_INDEX, Action::DETAIL)
    //         ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
    //     ;
    // }


    public function configureActions(Actions $actions): Actions
    {
        $user = $this->getUser();

        $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
        // EXEMPLE : seul l'admin peut modifier ou supprimer
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $actions
                ->disable(Action::EDIT)
                ->disable(Action::DELETE)
                ->disable(Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom', 'Nom du service'),
            SlugField::new('identifiant')->setTargetFieldName('nom')->onlyOnForms(),
            AssociationField::new('responsable', 'Responsable du service'),
            // AssociationField::new('poles_service', 'Poles')
            //     ->setFormTypeOptions([
            //         'by_reference' => true, // Important pour les relations ManyToMany
            //         'multiple' => true,
            //     ]),
            AssociationField::new('processus', 'Processus')
                ->setFormTypeOptions([
                    'by_reference' => true, // Important pour les relations ManyToMany
                    'multiple' => true,
                ]),
            BooleanField::new('isOnline', 'En ligne'),
        ];
    }
}
