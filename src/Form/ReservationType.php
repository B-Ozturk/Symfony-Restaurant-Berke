<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('persons', IntegerType::class , [
                'required' => true,
                'empty_data' => 'Persons',
                'attr' => array("min" => 1, "max" => 20, 'value' => 1)
            ])
            ->add('day', DateType::class, [
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y')+0),
                'months' => range(date('m'), date('m')+1),
                'days' => range(date('d'), date('d')+7),
            ])
            ->add('time', TimeType::class, [
                'widget' => 'single_text',
                'hours' => range(10, 23),
                'attr' => array('min' => '10:00', 'max' => '23:00')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}