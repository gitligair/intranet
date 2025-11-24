<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Poles;
use App\Entity\Tache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TacheMiniType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Nouvelle tâche']])
            ->add('importance', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Haute' => 'haute',
                    'Moyenne' => 'moyenne',
                    'Basse' => 'basse'
                ]
            ])
            ->add('deadline', DateType::class, [
                'label' => false,
                'widget' => 'single_text',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}
