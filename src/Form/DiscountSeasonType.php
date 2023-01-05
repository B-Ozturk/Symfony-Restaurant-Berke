<?php

namespace App\Form;

use App\Entity\DiscountSeason;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountSeasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
        'label' => 'Discount Season',
        'widget' => 'single_text',
        'years' => range(date('Y'), date('Y')+0),
        'months' => range(date('m'), date('m')+1),
        'days' => range(date('d'), date('d')+7),
    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DiscountSeason::class,
        ]);
    }
}
