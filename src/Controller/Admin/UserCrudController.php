<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
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
            TextField::new('password', 'mot de passe')->setFormType(PasswordType::class)->onlyWhenCreating(),
            // Champ de vérification du mot de passe
            TextField::new('password_')
                ->setFormType(PasswordType::class)
                ->setLabel('Vérification du mot de passe')
                ->setHelp('Veuillez entrer à nouveau votre mot de passe pour vérification') // 🚨 Ce champ ne sera pas stocké en base de données !
                ->setFormTypeOption('mapped', false)
                ->onlyWhenCreating(),  // Ce champ ne doit pas être mappé à l'entité
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Stagiaire' => 'ROLE_STAGIAIRE',
                    'Utilisateur' => 'ROLE_LIGAIR',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                ])
                ->allowMultipleChoices(),
            // ->renderExpanded(), // Affiche des cases à cocher
            SlugField::new('identifiant')->setTargetFieldName(['prenom', 'nom'])->onlyOnForms(),
            TextField::new('identifiant')->hideOnForm(),

        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        // Récupérer le mot de passe du formulaire (qui n'est pas stocké en base)
        $confirmPassword = $this->getContext()->getRequest()->get('User')['password_'];

        if ($entityInstance->getPassword() != $confirmPassword) {
            throw new \RuntimeException('Les mots de passe ne correspondent pas.');
        }

        if ($entityInstance->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setPassword($hashedPassword)
                ->setIsOnline(false);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updatetEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        // Récupérer le mot de passe du formulaire (qui n'est pas stocké en base)
        $confirmPassword = $this->getContext()->getRequest()->get('confirmPassword');

        if ($entityInstance->getPassword() !== $confirmPassword) {
            throw new \RuntimeException('Les mots de passe ne correspondent pas.');
        }

        if ($entityInstance->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setPassword($hashedPassword);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
