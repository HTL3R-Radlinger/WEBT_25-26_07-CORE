<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Meal;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $allergenCodes = ["A", "C", "D", "F", "G", "M", "N"];
        $allergens = array();
        for ($i = 0; $i < count($allergenCodes); $i++) {
            $allergen = new Allergen();
            $allergen->setCode($allergenCodes[$i]);
            $allergens[] = $allergen;
            $manager->persist($allergen);
        }

        $mealNames = ["Pizza", "Burger", "Nudeln", "Maki", "Wiener Schnitzel mit Erdäpfelsalat"];
        $meals = array();
        for ($i = 0; $i < count($mealNames); $i++) {
            $meal = new Meal();
            $meal->setName($mealNames[$i]);
            $meal->setNutritionalInfo("Calories: " . rand(400, 700));
            $anzAllergens = rand(1, 5);
            for ($j = 0; $j < $anzAllergens; $j++) {
                $meal->addAllergen($allergens[rand(0, count($allergens) - 1)]);
            }
            $meals[] = $meal;
            $manager->persist($meal);
        }

        $userNames = ["STF", "BUC", "BIS"];
        $users = array();
        for ($i = 0; $i < count($userNames); $i++) {
            $user = new User();
            $user->setName($userNames[$i]);
            $user->setAddress("HTL Rennweg, 1030 Wien");
            $users[] = $user;
            $manager->persist($user);
        }

        for ($i = 0; $i < 10; $i++) {
            $order = new Order();
            $order->setDate($this->randomPastDate());
            $order->setPrice(rand(10, 30));
            $order->setBuyer($users[rand(0, count($users) - 1)]);
            $anzMeals = rand(1, 5);
            for ($j = 0; $j < $anzMeals; $j++) {
                $order->addMeal($meals[rand(0, count($meals) - 1)]);
            }
            $manager->persist($order);
        }

        $manager->flush();
    }

    function randomPastDate(int $daysAgo = 365): DateTime
    {
        $timestamp = time() - mt_rand(0, $daysAgo * 86400);
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        return $date;
    }
}
