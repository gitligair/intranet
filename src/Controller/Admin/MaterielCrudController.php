<?php

namespace App\Controller\Admin;

use App\Entity\Ecran;
use App\Entity\Materiel;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\EcranCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\OrdinateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Controller\Admin\PetitmaterielCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MaterielCrudController extends AbstractCrudController
{
    private $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }


    public static function getEntityFqcn(): string
    {
        return Materiel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Matériel')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un matériel')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un matériel')
            ->setPageTitle(Crud::PAGE_DETAIL, fn(Materiel $materiel) => (string) $materiel)
            ->setEntityLabelInPlural('Matériels')
            ->setEntityLabelInSingular('Matériel')
            ->setSearchFields(['nom', 'utilisateur.nom', 'utilisateur.prenom'])
            ->setDefaultSort(['buyAt' => 'DESC']);
    }


    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, 'new')
            ->remove(Crud::PAGE_INDEX, 'edit')
            ->remove(Crud::PAGE_DETAIL, 'edit')
            ->remove(Crud::PAGE_DETAIL, 'delete')
            ->remove(Crud::PAGE_INDEX, 'delete')
            ->add(Crud::PAGE_INDEX, Action::new('voirDetails', 'Voir détails')
                ->linkToCrudAction('redirectToChild'));
    }

    public function redirectToChild(AdminContext $context, EntityManagerInterface $entityManager): RedirectResponse
    {

        // Méthode 2 : via les paramètres de requête (plus bas niveau)
        $idFromRequest = $context->getRequest()->query->get('entityId');
        $materiel = $entityManager->getRepository(Materiel::class)->find($idFromRequest);
        if (!$materiel) {
            throw $this->createNotFoundException('Le matériel n\'existe pas.');
        }


        if ($materiel->getType()->getId() === 1) {
            $url = $this->adminUrlGenerator
                ->setController(EcranCrudController::class)
                ->setAction(Crud::PAGE_DETAIL)
                ->setEntityId($materiel->getId())
                ->generateUrl();
            return new RedirectResponse($url);
        } elseif ($materiel->getType()->getId() === 2) {
            $url = $this->adminUrlGenerator
                ->setController(OrdinateurCrudController::class)
                ->setAction(Crud::PAGE_DETAIL)
                ->setEntityId($materiel->getId())
                ->generateUrl();

            return new RedirectResponse($url);
        } elseif ($materiel->getType()->getId() === 3) {
            $url = $this->adminUrlGenerator
                ->setController(PetitmaterielCrudController::class)
                ->setAction(Crud::PAGE_DETAIL)
                ->setEntityId($materiel->getId())
                ->generateUrl();
            return new RedirectResponse($url);
        } elseif ($materiel->getType()->getId() === 4) {
            $url = $this->adminUrlGenerator
                ->setController(AccessoireCrudController::class)
                ->setAction(Crud::PAGE_DETAIL)
                ->setEntityId($materiel->getId())
                ->generateUrl();
            return new RedirectResponse($url);
        } else {
            $url = $this->adminUrlGenerator
                ->setController(MaterielCrudController::class)
                ->setAction(Crud::PAGE_DETAIL)
                ->setEntityId($materiel->getId())
                ->generateUrl();
            return new RedirectResponse($url);
        }



        return new RedirectResponse($this->adminUrlGenerator->setController(MaterielCrudController::class)->generateUrl());
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('type', 'Type de matériel')
                ->hideOnForm(),
            TextField::new('nom', 'Nom')
                ->hideOnForm(),
            AssociationField::new('utilisateur', 'Utilisateur')
                ->hideOnForm()->hideOnIndex(),
            DateField::new('buyAt', 'Date d\'achat')
                ->hideOnForm(),
            MoneyField::new('prix', 'Prix')
                ->setCurrency('EUR')
                ->hideOnForm(),

        ];
    }
}
