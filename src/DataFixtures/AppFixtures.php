<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Meal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $mealNames = ["Pizza", "Burger", "Nudeln", "Maki", "Wiener Schnitzel mit Erdäpfelsalat"];
        for ($j = 0; $j < 5; $j++) {
            $meal = new Meal();
            $meal->setName($mealNames[$j]);
            $meal->setNutritionalInfo("Calories: " . rand(400, 700));
            $meal->setAllergens("Gluten, Lactose");
            $meal->setDate(new \DateTime());
            $manager->persist($meal);
        }

        $manager->flush();
    }
}
