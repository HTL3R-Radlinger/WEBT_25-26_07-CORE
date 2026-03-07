<?php

namespace App\Form;

use App\Entity\Allergen;
use App\Entity\Meal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('allergens', EntityType::class, [
                'class' => Allergen::class,
                'choice_label' => function (Allergen $allergen): string {
                    return $allergen->getCode();
                },
                'multiple' => true,
                'expanded' => true, // Checkboxen statt <select>
                'label' => 'Allergens',
                'required' => false,
                'by_reference' => false, // WICHTIG: damit addAllergenItem()/removeAllergenItem() aufgerufen wird
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
