<?php

namespace App\Controller\Admin;

use App\Entity\CotechVacarm;
use App\Entity\CotechVacarmMateriel;
use App\Form\CotechVacarmMaterielType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CotechVacarmCrudController extends AbstractCrudController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return CotechVacarm::class;
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Adherent  COTECH Vacarm')
            ->setEntityLabelInPlural('COTECH VACARM  ');
        // ->setEntityPermission('ROLE_SUPER_ADMIN');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            ImageField::new('image', 'Aasqa')
                ->setBasePath('/images/cotech_vacarm')
                ->setUploadDir('public/images/cotech_vacarm')
                ->hideOnForm(),
            TextField::new('nomAdherent'),
            SlugField::new('slugAdherent', 'Identifiant')->setTargetFieldName('nomAdherent')->onlyOnForms(),
            TextField::new('imageFile')->setFormType(VichImageType::class)->onlyOnForms(),
            DateField::new('addedAt', 'Date d\'adhesion'),
            DateTimeField::new('createdAt')->onlyOnDetail(),
            DateTimeField::new('updatedAt')->onlyOnDetail(),
            AssociationField::new('cotechVacarmMateriel', 'Materiel')
                ->renderAsEmbeddedForm(CotechVacarmMaterielCrudController::class)
                ->onlyOnForms(),
            AssociationField::new('cotechVacarmMateriel', 'Materiel')->hideOnForm(),


        ];
    }
}
