<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * User Entity - Represents a customer/buyer in the system
 *
 * Database table: `user` (backticks because "user" is SQL reserved word)
 *
 * This entity represents customers who can place orders.
 * Each user has:
 * - Name
 * - Address
 * - Collection of orders they've placed (One-to-Many relationship)
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]  // Backticks because 'user' is SQL reserved keyword
class User
{
    /**
     * Primary Key - Auto-incremented ID
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * User Name
     *
     * The customer's name or username
     * Examples: "STF", "BUC", "BIS" (appears to be abbreviations/initials)
     *
     * VARCHAR(255) in database
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * User Address
     *
     * Customer's delivery address
     * Example: "HTL Rennweg, 1030 Wien"
     *
     * VARCHAR(255) in database
     */
    #[ORM\Column(length: 255)]
    private ?string $address = null;

    /**
     * Orders Collection
     *
     * Contains all orders placed by this user
     *
     * @var Collection<int, Order> Collection of Order objects
     *
     * #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'buyer', orphanRemoval: true)]
     * - One-to-Many relationship (one user has many orders)
     * - targetEntity: Order::class -> Collection contains Order objects
     * - mappedBy: 'buyer' -> References the $buyer property in Order entity
     * - orphanRemoval: true -> If order is removed from this collection, it's deleted from database
     *
     * "mappedBy" indicates this is the INVERSE side of the relationship
     * (the Order entity owns the relationship via the buyer_id foreign key)
     *
     * orphanRemoval explained:
     * - If you do: $user->removeOrder($order), the order is deleted from database
     * - Useful when child entities (orders) shouldn't exist without parent (user)
     * - In this case, might not be ideal since you may want to keep order history
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'buyer', orphanRemoval: true)]
    private Collection $orders;

    /**
     * Constructor
     *
     * Initializes the orders collection as empty ArrayCollection
     * This prevents null pointer errors when working with the orders
     */
    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    /**
     * Get User ID
     *
     * @return int|null The user ID, or null if not yet saved to database
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get User Name
     *
     * @return string|null The user's name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set User Name
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
     * Get User Address
     *
     * @return string|null The user's address
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * Set User Address
     *
     * @param string $address The address to set
     * @return static Returns $this for method chaining
     */
    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get All Orders Placed by This User
     *
     * @return Collection<int, Order> Collection of Order objects
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    /**
     * Add an Order to This User
     *
     * This method maintains bidirectional relationship consistency.
     * When adding an order to a user:
     * 1. Add order to user's collection
     * 2. Set this user as the order's buyer
     *
     * @param Order $order The order to add
     * @return static Returns $this for method chaining
     */
    public function addOrder(Order $order): static
    {
        // Check if order is not already in the collection (prevent duplicates)
        if (!$this->orders->contains($order)) {
            // Add order to this user's collection
            $this->orders->add($order);

            // Make sure the order knows this user is the buyer
            // This maintains the bidirectional relationship
            $order->setBuyer($this);
        }

        return $this;
    }

    /**
     * Remove an Order from This User
     *
     * This method maintains bidirectional relationship consistency.
     * When removing an order:
     * 1. Remove from user's collection
     * 2. Clear the buyer reference in the order (if it points to this user)
     *
     * Note: Due to orphanRemoval: true, removing an order here will also
     * DELETE it from the database when flush() is called!
     *
     * @param Order $order The order to remove
     * @return static Returns $this for method chaining
     */
    public function removeOrder(Order $order): static
    {
        // Try to remove the order from the collection
        if ($this->orders->removeElement($order)) {
            // If successful, check if this user is still set as the buyer
            // If so, clear the buyer reference (set to null)
            if ($order->getBuyer() === $this) {
                $order->setBuyer(null);
            }

            // IMPORTANT: Due to orphanRemoval: true, this order will be
            // DELETED from the database when $em->flush() is called!
        }

        return $this;
    }
}
