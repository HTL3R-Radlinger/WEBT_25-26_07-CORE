<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MealRepository::class)]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $nutritionalInfo = null;

    /**
     * @var Collection<int, Allergens>
     */
    #[ORM\ManyToMany(targetEntity: Allergens::class, mappedBy: 'meals')]
    private Collection $allergens;

    public function __construct()
    {
        $this->allergens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getNutritionalInfo(): ?string
    {
        return $this->nutritionalInfo;
    }

    public function setNutritionalInfo(string $nutritionalInfo): static
    {
        $this->nutritionalInfo = $nutritionalInfo;

        return $this;
    }

    /**
     * @return Collection<int, Allergens>
     */
    public function getAllergens(): Collection
    {
        return $this->allergens;
    }

    public function addAllergen(Allergens $allergen): static
    {
        if (!$this->allergens->contains($allergen)) {
            $this->allergens->add($allergen);
            $allergen->addMeal($this);
        }

        return $this;
    }

    public function removeAllergen(Allergens $allergen): static
    {
        if ($this->allergens->removeElement($allergen)) {
            $allergen->removeMeal($this);
        }

        return $this;
    }
}
