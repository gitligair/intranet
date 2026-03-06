<?php

namespace App\Controller\Admin;

use App\Entity\Formulaire;
use App\Services\FormulaireAgileService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class FormulaireCrudController extends AbstractCrudController
{
    public function __construct(
        private FormulaireAgileService $formulaireAgileService
    ) {}

    public static function getEntityFqcn(): string
    {
        return Formulaire::class;
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            $responseParameters->set('poles', $this->formulaireAgileService->getPoles());
        }

        return $responseParameters;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Formulaire')
            ->setEntityLabelInPlural('Formulaires')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined()
            // ✅ surcharge le template du detail
            ->overrideTemplate('crud/detail', 'admin/formulaire_agile/detail_formulaire.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT);
    }



    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            DateField::new('dateJour', 'Date'),
            AssociationField::new('animateur', 'Animateur'),
            AssociationField::new('pointsCles', 'Points clé de la réunion')->renderAsHtml(),
            AssociationField::new('partageInfos', 'Partage d\'infos')->renderAsHtml(),
            AssociationField::new('tachesPrioritaires', 'Tâches prioritaires')->renderAsHtml(),
            BooleanField::new('reunionTenue', 'Réunion tenue')->renderAsSwitch(false),
            DateTimeField::new('prochaineReunionAt', 'Prochaine réunion')->hideOnIndex(),
            AssociationField::new('prochainAnimateur', 'Animateur prochaine')->hideOnIndex(),
        ];
    }
}
