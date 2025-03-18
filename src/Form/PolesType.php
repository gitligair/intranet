<?php

namespace App\Form;

use App\Entity\Poles;
use App\Entity\Processus;
use App\Entity\Services;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('isOnline', null, [
                'label' => 'En ligne',
            ])
            ->add('processus', EntityType::class, [
                'class' => Processus::class,
                'choice_label' => 'nom',
                'multiple' => false,
                'expanded' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Poles::class,
        ]);
    }
}