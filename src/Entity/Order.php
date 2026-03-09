<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Order Entity - Represents a customer order
 *
 * Database table: `order` (backticks because "order" is SQL reserved word)
 *
 * An order contains:
 * - Order date
 * - Total price
 * - Multiple meals (Many-to-Many)
 * - One buyer/customer (Many-to-One)
 * - Current status (pending, confirmed, preparing, ready, delivered, cancelled)
 *
 * Status workflow:
 * pending -> confirmed -> preparing -> ready -> delivered
 * (can be cancelled at any point)
 */
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]  // Backticks because 'order' is SQL reserved keyword
class Order
{
    /**
     * Status Constants
     *
     * These constants define all valid order statuses.
     * Using constants prevents typos and makes code more maintainable.
     */
    public const string STATUS_PENDING = 'pending';
    public const string STATUS_CONFIRMED = 'confirmed';
    public const string STATUS_PREPARING = 'preparing';
    public const string STATUS_READY = 'ready';
    public const string STATUS_DELIVERED = 'delivered';
    public const string STATUS_CANCELLED = 'cancelled';

    /**
     * Primary Key - Auto-incremented ID
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Order Date
     *
     * The date when the order was placed
     *
     * #[ORM\Column(type: Types::DATE_MUTABLE)]
     * - DATE_MUTABLE: Stores only the date (not time)
     * - MUTABLE: DateTime object can be modified after creation
     *
     * Note: For date+time, use Types::DATETIME_MUTABLE instead
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    /**
     * Order Price
     *
     * Total price of the order in cents (or smallest currency unit)
     * Example: 2500 = $25.00 or €25.00
     *
     * Using integers for money prevents floating-point rounding errors
     */
    #[ORM\Column]
    private ?int $price = null;

    /**
     * Ordered Meals Collection
     *
     * Contains all meals included in this order
     *
     * @var Collection<int, Meal>
     *
     * #[ORM\ManyToMany(targetEntity: Meal::class)]
     * - Many-to-Many relationship (unidirectional)
     * - One order can have many meals
     * - One meal can be in many orders
     * - No "inversedBy" means Meal entity doesn't know about orders
     *
     * Database: Creates join table "order_meal" with columns: order_id, meal_id
     *
     * Note: Property name "Meals" (capital M) - should ideally be lowercase "meals"
     */
    #[ORM\ManyToMany(targetEntity: Meal::class)]
    private Collection $Meals;

    /**
     * Buyer (Customer) Relationship
     *
     * The user who placed this order
     *
     * #[ORM\ManyToOne(inversedBy: 'orders')]
     * - Many-to-One relationship (many orders belong to one user)
     * - inversedBy: 'orders' -> User entity has $orders collection
     *
     * #[ORM\JoinColumn(nullable: false)]
     * - Foreign key cannot be null (every order must have a buyer)
     * - Creates buyer_id column in order table
     */
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $buyer = null;

    /**
     * Order Status
     *
     * Current status of the order
     *
     * #[ORM\Column(length: 20, options: ['default' => 'pending'])]
     * - VARCHAR(20) column
     * - Default value: 'pending' (set by database when inserting new orders)
     *
     * PHP default value ensures new Order objects start as 'pending'
     * even before saving to database
     */
    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    private string $status = self::STATUS_PENDING;

    /**
     * Constructor
     *
     * Initializes the Meals collection as empty ArrayCollection
     */
    public function __construct()
    {
        $this->Meals = new ArrayCollection();
    }

    /**
     * Get Order ID
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get Order Date
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * Set Order Date
     *
     * @param \DateTime $date The date to set
     * @return static Returns $this for method chaining
     */
    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get Order Price
     *
     * @return int|null Price in cents/smallest currency unit
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * Set Order Price
     *
     * @param int $price Price in cents/smallest currency unit
     * @return static Returns $this for method chaining
     */
    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get All Meals in This Order
     *
     * @return Collection<int, Meal> Collection of Meal objects
     */
    public function getMeals(): Collection
    {
        return $this->Meals;
    }

    /**
     * Add a Meal to This Order
     *
     * @param Meal $meal The meal to add
     * @return static Returns $this for method chaining
     */
    public function addMeal(Meal $meal): static
    {
        // Only add if not already in the order (prevent duplicates)
        if (!$this->Meals->contains($meal)) {
            $this->Meals->add($meal);
        }

        return $this;
    }

    /**
     * Remove a Meal from This Order
     *
     * @param Meal $meal The meal to remove
     * @return static Returns $this for method chaining
     */
    public function removeMeal(Meal $meal): static
    {
        $this->Meals->removeElement($meal);

        return $this;
    }

    /**
     * Get the Buyer (Customer) of This Order
     *
     * @return User|null The user who placed the order
     */
    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    /**
     * Set the Buyer (Customer) of This Order
     *
     * @param User|null $buyer The user who placed the order
     * @return static Returns $this for method chaining
     */
    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;

        return $this;
    }

    /**
     * Get Current Order Status
     *
     * @return string One of the STATUS_* constants
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set Order Status with Validation
     *
     * This method validates that the new status is one of the allowed values.
     * This prevents typos and invalid status values from being saved.
     *
     * Validation process:
     * 1. Define array of all allowed status values
     * 2. Check if provided status is in that array
     * 3. If not valid, throw an exception
     * 4. If valid, set the status
     *
     * @param string $status New status value
     * @return static Returns $this for method chaining
     * @throws \InvalidArgumentException If status is not one of the allowed values
     */
    public function setStatus(string $status): static
    {
        // Array of all allowed status values
        $allowed = [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PREPARING,
            self::STATUS_READY,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ];

        // Validate: check if status is in the allowed array
        if (!in_array($status, $allowed)) {
            // Throw exception if invalid - this will be caught by the controller
            throw new \InvalidArgumentException("Invalid status: $status");
        }

        // Status is valid, set it
        $this->status = $status;

        return $this;
    }
}
