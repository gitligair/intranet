<?php

namespace App\Controller\Admin;

use App\Entity\CotechVacarm;
use App\Entity\CotechVacarmMateriel;
use App\Form\CotechVacarmMaterielBdType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CotechVacarmMaterielDetailType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CotechVacarmMaterielCrudController extends AbstractCrudController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return CotechVacarmMateriel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Caracteristiques serveur')
            ->setEntityLabelInPlural('Caracteristiques serveurs');
        // ->setEntityPermission('ROLE_SUPER_ADMIN');
    }



    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('adherent')->hideOnForm(),

            FormField::addPanel('Données Générales')->setIcon('fa fa-box'),
            TextField::new('adresseIp', 'Adresse IP'),
            NumberField::new('port', 'Port'),
            TextField::new('identifiant', 'Identifiant'),
            TextField::new('motdepasse', 'Mot de passe')->hideOnIndex(),

            FormField::addPanel('Détails du système d\'exploitation')->setIcon('fa fa-desktop'),
            TextField::new('os', 'Système d\'exploitation')->hideOnForm(),
            ChoiceField::new('os', 'Système d\'exploitation')
                ->allowMultipleChoices(false)
                ->autocomplete()
                ->setChoices(
                    [
                        'Windows' => 'Windows',
                        'Linux' => 'Linux',
                        'macOs' => 'macOs',
                        'Autre' => 'Autre',
                    ]
                )
                ->onlyOnForms(),
            TextField::new('detailOs', 'Détails du système d\'exploitation'),
            AssociationField::new('cotechVacarmMaterielDetail', 'Détails du matériel')
                ->renderAsEmbeddedForm(CotechVacarmMaterielDetailCrudController::class)
                ->onlyOnForms(),
            AssociationField::new('cotechVacarmMaterielDetail', 'Lien Code source')->hideOnForm(),

            FormField::addPanel('Bases de données')->setIcon('fa fa-database'),
            CollectionField::new('bds', 'Bases de données')
                ->setFormType(CollectionType::class)
                ->setFormTypeOptions([
                    'entry_type' => CotechVacarmMaterielBdType::class,
                    'by_reference' => false, // important pour l'ajout/suppression
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                ])
        ];
    }
}