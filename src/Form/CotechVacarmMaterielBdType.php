<?php

namespace App\Form;

use App\Entity\CotechVacarmMateriel;
use App\Entity\CotechVacarmMaterielBd;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CotechVacarmMaterielBdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('base', TextType::class, ['label' => 'Nom de la base de données'])
            ->add('host', TextType::class, ['label' => 'Hôte'])
            ->add('user', TextType::class, ['label' => 'Utilisateur'])
            ->add('password', TextType::class, ['label' => 'Mot de passe'])
            ->add('port', NumberType::class, ['label' => 'Port'])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'MySQL' => 'MySQL',
                    'PostgreSQL' => 'PostgreSQL',
                    'Oracle' => 'Oracle',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CotechVacarmMaterielBd::class,
        ]);
    }
}
