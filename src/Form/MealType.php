<?php

namespace App\Form;

use App\Entity\Allergen;
use App\Entity\Meal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MealType - Form definition for creating/editing Meal entities
 *
 * This form class defines:
 * - Which fields to display
 * - What type each field should be
 * - Labels and validation rules
 *
 * The form is used in AdminController for both adding and editing meals.
 */
class MealType extends AbstractType
{
    /**
     * Build the Form Structure
     *
     * This method defines all form fields and their configuration.
     * Each ->add() call creates a form field.
     *
     * @param FormBuilderInterface $builder The form builder object
     * @param array $options Additional options (not used here)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // --- FIELD 1: Meal Name ---
            // Creates a text input field for the meal name
            ->add('name', TextType::class, [
                'label' => 'Meal Name',
                'required' => true,
                'constraints' => [
                    new Assert\Length(max: 255),
                ],
            ])

            // --- FIELD 2: Allergens (Multi-select Checkboxes) ---
            // Creates a checkbox group for selecting allergens
            ->add('allergens', EntityType::class, [
                // EntityType is used for selecting entities from database

                'class' => Allergen::class,   // Which entity to select from

                // How to display each allergen option
                // This function determines what text is shown for each checkbox
                'choice_label' => function (Allergen $allergen): string {
                    return $allergen->getCode();  // Display the code (e.g., "A", "C", "D")
                },

                'multiple' => true,           // Allow selecting multiple allergens

                // expanded: true -> Checkboxes
                // expanded: false -> Dropdown select
                'expanded' => true,           // Render as checkboxes instead of dropdown

                'label' => 'Allergens',       // Label for the checkbox group
                'required' => false,          // Allergens are optional (not all meals have allergens)

                // IMPORTANT: by_reference = false
                // This tells Symfony to use addAllergen()/removeAllergen() methods
                // instead of directly replacing the collection.
                // Required for Many-to-Many relationships to work correctly!
                //
                // With by_reference: false:
                // - Symfony calls $meal->addAllergen() for each selected allergen
                // - Symfony calls $meal->removeAllergen() for deselected allergens
                //
                // With by_reference: true (default):
                // - Symfony would replace entire collection (breaks bidirectional sync)
                'by_reference' => false,
            ])

            // --- FIELD 3: Nutritional Info ---
            // Creates a text input for nutritional information
            ->add('nutritionalInfo', TextType::class, [
                'label' => 'Nutritional Info (e.g. "Calories: 700")',  // Helpful label with example
                'required' => true,                                     // Field is mandatory
                'constraints' => [
                    new Assert\Length(max: 255),
                ],
            ]);
    }

    /**
     * Configure Form Options
     *
     * This method sets default options for the form.
     * Most importantly, it connects the form to the Meal entity.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Connect this form to the Meal entity
            // Symfony will:
            // - Auto-populate form fields from entity properties
            // - Auto-fill entity properties from submitted form data
            'data_class' => Meal::class,
        ]);
    }
}
