<?php

namespace App\Form;

use App\Entity\Menu;
use \Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description', TextareaType::class)
            ->add('price', IntegerType::class , [
                'required' => true,
                'empty_data' => 'Persons',
                'attr' => array("min" => 1, "max" => 100)
            ])
            ->add('picture', FileType::class, array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('category')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
