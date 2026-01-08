<?php

namespace App\Controller\Admin;

use App\Entity\CotechVacarm;
use App\Entity\CotechVacarmMateriel;
use App\Form\CotechVacarmMaterielBdType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CotechVacarmMaterielDetailType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CotechVacarmMaterielCrudController extends AbstractCrudController
{
    private $em;

    public function __construct(EntityManagerInterface $em, private AdminUrlGenerator $adminUrlGenerator)
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

    public function configureActions(Actions $actions): Actions
    {
        return $actions

            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_SUPER_ADMIN')
        ;
    }

    public function detail(AdminContext $context)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('warning', 'Vous n\'avez pas les permissions nécessaires.');
            // Redirection vers la liste de ce même CRUD
            // Construire l'URL vers l'index du CRUD courant
            $url = $this->adminUrlGenerator
                ->setController(static::class)
                ->setAction('index')
                ->generateUrl();

            return new RedirectResponse($url);
        }

        return parent::detail($context);
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
                ->onlyOnForms(),
            CollectionField::new('bds', 'Bases de données')
                ->setTemplatePath('admin/fields/bds_table.html.twig')
                ->onlyOnDetail(),
        ];
    }
}
