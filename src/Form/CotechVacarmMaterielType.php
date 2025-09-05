<?php

namespace App\Form;

use App\Entity\CotechVacarm;
use App\Entity\CotechVacarmMateriel;
use App\Entity\CotechVacarmMaterielDetail;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CotechVacarmMaterielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresseIp')
            ->add('port')
            ->add('identifiant')
            ->add('motdepasse')
            ->add('os')
            ->add('detailOs')
            // ->add('adherent', EntityType::class, [
            //     'class' => CotechVacarm::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('cotechVacarmMaterielDetail', EntityType::class, [
            //     'class' => CotechVacarmMaterielDetail::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CotechVacarmMateriel::class,
        ]);
    }
}