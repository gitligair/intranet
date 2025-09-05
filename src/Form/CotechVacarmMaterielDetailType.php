<?php

namespace App\Form;

use App\Entity\CotechVacarmMateriel;
use Symfony\Component\Form\AbstractType;
use App\Entity\CotechVacarmMaterielDetail;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CotechVacarmMaterielDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sourceVacarm', TextType::class, [
                'label' => 'Source VACARM',
                'required' => false,
            ])
            // ->add('materiel', EntityType::class, [
            //     'class' => CotechVacarmMateriel::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CotechVacarmMaterielDetail::class,
        ]);
    }
}
