<?php

namespace App\Controller\Admin;

use App\Entity\CotechVacarmMaterielDetail;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CotechVacarmMaterielDetailCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CotechVacarmMaterielDetail::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('sourceVacarm', 'url de source VACARM'),
            AssociationField::new('materiel')->hideOnForm(),
        ];
    }
}
