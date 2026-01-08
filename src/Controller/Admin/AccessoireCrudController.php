<?php

namespace App\Controller\Admin;

use App\Entity\Accessoire;
use App\Entity\TypeMateriel;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AccessoireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Accessoire::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Accessoire')
            ->setEntityLabelInPlural('Accessoires (cables hdmi,souris,casques,multiprises ...)');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_SUPER_ADMIN')
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Données Générales')->setIcon('fa fa-box'),
            ChoiceField::new('typeAccessoire', 'Accessoire')
                ->autocomplete()
                ->setChoices(
                    [
                        'Cable HDMI' => 'Cable HDMI',
                        'Multiprise' => 'Multipprise',
                        'Casque audio' => 'Casque Audio',
                        'Souris' => 'Souris'
                    ]
                )
                ->setHelp('Exemple : Casque Audio'),

            FormField::addPanel('Données Complémentaires')->setIcon('fa fa-box'),
            IntegerField::new('quantite', 'Total'),
            IntegerField::new('stockDisponible', 'Quantite disponible')
                ->setFormTypeOption('disabled', true),
            TextField::new('remarques', 'Remarques'),

            FormField::addPanel('Affectation')->setIcon('fa fa-user'),
            BooleanField::new('isStock', 'En stock')->hideOnIndex(),
            AssociationField::new('localisation', 'Bureau')
                ->setCssClass('enStock')
                ->setFormTypeOption('disabled', false)
                ->setHelp('Bureau où se trouve le matériel')
                ->hideOnIndex(),
            AssociationField::new('listeAlloues', 'Utilisateurs alloués')
                ->autocomplete()
                // IMPORTANT pour ManyToMany : EasyAdmin doit appeler add/remove
                ->setFormTypeOption('by_reference', false)
                ->setHelp('Chaque utilisateur = 1 unité allouée'),

        ];
    }

    private function syncStockOrThrow(Accessoire $a): void
    {
        $total = (int) $a->getQuantite();
        $alloues = $a->getListeAlloues()->count();

        if ($alloues > $total) {
            throw new \RuntimeException("Impossible : $alloues utilisateurs alloués pour un total de $total.");
        }

        $a->setStockDisponible($total - $alloues);

        // Si tu veux garder isStock cohérent avec "il reste du stock"
        $a->setIsStock(($total - $alloues) > 0);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Accessoire) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }
        $entityInstance->setNom($entityInstance->getTypeAccessoire());

        $this->syncStockOrThrow($entityInstance);

        $typeMateriel = new TypeMateriel();
        $typeMateriel = $entityManager->getRepository(TypeMateriel::class)->findOneBy(['nom' => 'Accessoire']);

        if ($typeMateriel) {
            $entityInstance->setType($typeMateriel);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Accessoire) {
            parent::updateEntity($entityManager, $entityInstance);
            return;
        }

        $this->syncStockOrThrow($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }
}
