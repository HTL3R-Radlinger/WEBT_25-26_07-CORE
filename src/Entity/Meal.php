<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Meal Entity - Represents a meal/dish in the ordering system
 *
 * Database table: meal
 *
 * This entity represents individual meals that can be ordered.
 * Each meal has:
 * - A name (e.g., "Pizza", "Burger")
 * - Nutritional information (e.g., "Calories: 650")
 * - Associated allergens (Many-to-Many relationship)
 *
 * Relationships:
 * - Many-to-Many with Allergen (one meal can have multiple allergens)
 */
#[ORM\Entity(repositoryClass: MealRepository::class)]
class Meal
{
    /**
     * Primary Key - Auto-incremented ID
     *
     * Unique identifier for each meal
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Meal Name
     *
     * The display name of the meal
     * Examples: "Pizza Margherita", "Cheeseburger", "Caesar Salad"
     *
     * VARCHAR(255) in the database
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Nutritional Information
     *
     * Text description of nutritional content
     * Example: "Calories: 650, Protein: 25g, Fat: 30g"
     *
     * VARCHAR(255) in the database
     */
    #[ORM\Column(length: 255)]
    private ?string $nutritionalInfo = null;

    /**
     * Related Allergens Collection
     *
     * Contains all allergens present in this meal
     *
     * @var Collection<int, Allergen> Collection of Allergen objects
     *
     * #[ORM\ManyToMany] defines Many-to-Many relationship
     * targetEntity: Allergen::class -> Collection contains Allergen objects
     * inversedBy: 'meals' -> References the $meals property in Allergen entity
     *
     * "inversedBy" indicates this is the OWNING side of the relationship
     * (this entity manages the join table: meal_allergen)
     *
     * Database structure:
     * Table: meal_allergen
     * Columns: meal_id, allergen_id
     */
    #[ORM\ManyToMany(targetEntity: Allergen::class, inversedBy: 'meals')]
    private Collection $allergens;

    /**
     * Constructor
     *
     * Initializes the allergens collection when creating a new Meal
     * This prevents null pointer errors when working with the collection
     */
    public function __construct()
    {
        // Create empty collection for allergens
        $this->allergens = new ArrayCollection();
    }

    /**
     * Get Meal ID
     *
     * @return int|null The meal ID, or null if not yet saved to database
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get Meal Name
     *
     * @return string|null The name of the meal
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set Meal Name
     *
     * @param string $name The name to set
     * @return static Returns $this for method chaining
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Nutritional Information
     *
     * @return string|null The nutritional information text
     */
    public function getNutritionalInfo(): ?string
    {
        return $this->nutritionalInfo;
    }

    /**
     * Set Nutritional Information
     *
     * @param string $nutritionalInfo The nutritional info to set
     * @return static Returns $this for method chaining
     */
    public function setNutritionalInfo(string $nutritionalInfo): static
    {
        $this->nutritionalInfo = $nutritionalInfo;

        return $this;
    }

    /**
     * Get All Allergens in This Meal
     *
     * @return Collection<int, Allergen> Collection of Allergen objects
     */
    public function getAllergens(): Collection
    {
        return $this->allergens;
    }

    /**
     * Add an Allergen to This Meal
     *
     * Important: This is the OWNING side of the relationship, so this method
     * is simpler than in the Allergen entity. We don't need to update the
     * reverse side because Doctrine will handle that based on the join table.
     *
     * However, for consistency in bidirectional relationships, you might
     * still want to call $allergen->addMeal($this) to keep both sides in sync
     * during the same request (before flush).
     *
     * @param Allergen $allergen The allergen to add
     * @return static Returns $this for method chaining
     */
    public function addAllergen(Allergen $allergen): static
    {
        // Only add if not already present (prevent duplicates)
        if (!$this->allergens->contains($allergen)) {
            // Add allergen to collection
            // Doctrine will insert a row in the meal_allergen join table
            $this->allergens->add($allergen);
        }

        return $this;
    }

    /**
     * Remove an Allergen from This Meal
     *
     * Removes the allergen from this meal's collection.
     * Doctrine will delete the corresponding row from the meal_allergen join table.
     *
     * @param Allergen $allergen The allergen to remove
     * @return static Returns $this for method chaining
     */
    public function removeAllergen(Allergen $allergen): static
    {
        // Remove allergen from collection
        // Doctrine will delete the row from meal_allergen join table
        $this->allergens->removeElement($allergen);

        return $this;
    }
}
