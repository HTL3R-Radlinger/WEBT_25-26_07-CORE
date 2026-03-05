<?php

namespace App\Form;

use App\Entity\Meal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Meal Name',
                'required' => true,
            ])
            ->add('allergens', TextType::class, [
                'label' => 'Allergens (e.g. "Gluten, Lactose")',
                'required' => true,
            ])
            ->add('nutritionalInfo', TextType::class, [
                'label' => 'Nutritional Info (e.g. "Calories: 700")',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meal::class,
        ]);
    }
}
