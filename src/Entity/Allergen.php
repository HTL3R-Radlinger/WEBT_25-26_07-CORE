<?php

namespace App\Entity;

use App\Repository\AllergenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Allergen Entity - Represents food allergens
 *
 * Database table: allergen
 *
 * This entity represents allergens (like "A" for gluten, "C" for eggs, etc.)
 * that can be associated with meals.
 *
 * Relationship: Many-to-Many with Meal
 * - One allergen can be in many meals
 * - One meal can have many allergens
 * - This is the "inverse" side of the relationship (Meal is the "owning" side)
 */
#[ORM\Entity(repositoryClass: AllergenRepository::class)]
class Allergen
{
    /**
     * Primary Key - Auto-incremented ID
     *
     * #[ORM\Id] marks this as the primary key
     * #[ORM\GeneratedValue] means database will auto-generate the value
     * #[ORM\Column] means this is stored in the database
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Allergen Code
     *
     * Single letter code representing the allergen
     * Examples: "A" = gluten, "C" = eggs, "D" = fish, "F" = soy, etc.
     *
     * #[ORM\Column(length: 255)] creates a VARCHAR(255) column
     */
    #[ORM\Column(length: 255)]
    private ?string $code = null;

    /**
     * Related Meals Collection
     *
     * Contains all meals that have this allergen
     *
     * @var Collection<int, Meal> Collection of Meal objects
     *
     * #[ORM\ManyToMany] defines the relationship type
     * targetEntity: Meal::class -> This collection contains Meal objects
     * mappedBy: 'allergens' -> References the $allergens property in the Meal entity
     *
     * "mappedBy" indicates this is the INVERSE side of the relationship
     * (the Meal entity is responsible for managing the relationship)
     */
    #[ORM\ManyToMany(targetEntity: Meal::class, mappedBy: 'allergens')]
    private Collection $meals;

    /**
     * Constructor
     *
     * Called when creating a new Allergen object
     * Initializes the meals collection as an empty ArrayCollection
     *
     * This is necessary for all collection properties to avoid null pointer errors
     */
    public function __construct()
    {
        // Initialize empty collection - prevents null errors when adding meals
        $this->meals = new ArrayCollection();
    }

    /**
     * Get ID
     *
     * @return int|null The allergen ID, or null if not yet persisted to database
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get Allergen Code
     *
     * @return string|null The allergen code (e.g., "A", "C", "D")
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Set Allergen Code
     *
     * @param string $code The allergen code to set
     * @return static Returns $this for method chaining
     */
    public function setCode(string $code): static
    {
        $this->code = $code;

        // Return $this to allow method chaining: $allergen->setCode("A")->addMeal($meal);
        return $this;
    }

    /**
     * Get All Meals Containing This Allergen
     *
     * @return Collection<int, Meal> Collection of Meal objects
     */
    public function getMeals(): Collection
    {
        return $this->meals;
    }

    /**
     * Add a Meal to This Allergen
     *
     * This method maintains bidirectional relationship consistency:
     * - Adds meal to this allergen's collection
     * - Ensures the meal also knows about this allergen
     *
     * @param Meal $meal The meal to associate with this allergen
     * @return static Returns $this for method chaining
     */
    public function addMeal(Meal $meal): static
    {
        // Check if meal is not already in the collection (prevent duplicates)
        if (!$this->meals->contains($meal)) {
            // Add meal to this allergen's collection
            $this->meals->add($meal);

            // Make sure the meal also knows about this allergen
            // This maintains the bidirectional relationship
            $meal->addAllergen($this);
        }

        return $this;
    }

    /**
     * Remove a Meal from This Allergen
     *
     * This method maintains bidirectional relationship consistency:
     * - Removes meal from this allergen's collection
     * - Ensures the meal no longer references this allergen
     *
     * @param Meal $meal The meal to disassociate from this allergen
     * @return static Returns $this for method chaining
     */
    public function removeMeal(Meal $meal): static
    {
        // Try to remove the meal from this collection
        if ($this->meals->removeElement($meal)) {
            // If successful, also remove this allergen from the meal
            // This maintains the bidirectional relationship
            $meal->removeAllergen($this);
        }

        return $this;
    }
}
