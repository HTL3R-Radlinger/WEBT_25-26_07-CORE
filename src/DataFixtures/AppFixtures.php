<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Meal;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * AppFixtures - Loads test data into the database
 *
 * This class generates sample data for development and testing.
 * Run with: php bin/console doctrine:fixtures:load
 *
 * WARNING: This will DELETE all existing data and replace it with test data!
 *
 * What this fixture creates:
 * - 7 allergens (A, C, D, F, G, M, N)
 * - 5 meals with random allergens
 * - 3 users
 * - 10 orders with random dates, buyers, and meals
 */
class AppFixtures extends Fixture
{
    /**
     * Load Data into Database
     *
     * This is the main method called by Symfony when loading fixtures.
     *
     * Process:
     * 1. Create and persist allergens
     * 2. Create and persist meals (with allergens)
     * 3. Create and persist users
     * 4. Create and persist orders (with users and meals)
     * 5. Flush everything to database at once
     *
     * @param ObjectManager $manager Doctrine's object manager for persisting entities
     */
    public function load(ObjectManager $manager): void
    {
        // ===== STEP 1: Create Allergens =====

        // Define allergen codes (European allergen labeling system)
        // A=gluten, C=eggs, D=fish, F=soy, G=milk, M=shellfish, N=nuts
        $allergenCodes = ["A", "C", "D", "F", "G", "M", "N"];

        // Array to store created allergen objects for later use
        $allergens = array();

        // Loop through each allergen code
        for ($i = 0; $i < count($allergenCodes); $i++) {
            // Create new Allergen entity
            $allergen = new Allergen();

            // Set the allergen code
            $allergen->setCode($allergenCodes[$i]);

            // Add to array for later reference
            $allergens[] = $allergen;

            // Tell Doctrine to track this object for saving
            $manager->persist($allergen);
        }

        // ===== STEP 2: Create Meals =====

        // Define meal names
        $mealNames = ["Pizza", "Burger", "Nudeln", "Maki", "Wiener Schnitzel mit Erdäpfelsalat"];

        // Array to store created meal objects
        $meals = array();

        // Loop through each meal name
        for ($i = 0; $i < count($mealNames); $i++) {
            // Create new Meal entity
            $meal = new Meal();

            // Set meal name
            $meal->setName($mealNames[$i]);

            // Generate random nutritional info (400-700 calories)
            $meal->setNutritionalInfo("Calories: " . rand(400, 700));

            // Add random allergens to this meal
            // Each meal gets 1-5 random allergens
            $anzAllergens = rand(1, 5);  // Random number between 1 and 5

            for ($j = 0; $j < $anzAllergens; $j++) {
                // Pick a random allergen from the allergens array
                $randomAllergen = $allergens[rand(0, count($allergens) - 1)];

                // Add it to this meal
                // Note: addAllergen() prevents duplicates, so it's safe if we pick the same one twice
                $meal->addAllergen($randomAllergen);
            }

            // Add meal to array for later use with orders
            $meals[] = $meal;

            // Track meal for saving
            $manager->persist($meal);
        }

        // ===== STEP 3: Create Users =====

        // Define user names (appear to be initials/abbreviations)
        $userNames = ["STF", "BUC", "BIS"];

        // Array to store created user objects
        $users = array();

        // Loop through each user name
        for ($i = 0; $i < count($userNames); $i++) {
            // Create new User entity
            $user = new User();

            // Set user name
            $user->setName($userNames[$i]);

            // Set same address for all users (sample school address)
            $user->setAddress("HTL Rennweg, 1030 Wien");

            // Add to array for later use with orders
            $users[] = $user;

            // Track user for saving
            $manager->persist($user);
        }

        // ===== STEP 4: Create Orders =====

        // Create 10 random orders
        for ($i = 0; $i < 10; $i++) {
            // Create new Order entity
            $order = new Order();

            // Set random past date (within last 365 days)
            $order->setDate($this->randomPastDate());

            // Set random price between 10 and 30 (euros/dollars/etc.)
            $order->setPrice(rand(10, 30));

            // Assign random buyer from users array
            $randomBuyer = $users[rand(0, count($users) - 1)];
            $order->setBuyer($randomBuyer);

            // Add random meals to this order
            // Each order gets 1-5 random meals
            $anzMeals = rand(1, 5);

            for ($j = 0; $j < $anzMeals; $j++) {
                // Pick a random meal from meals array
                $randomMeal = $meals[rand(0, count($meals) - 1)];

                // Add to order
                // Note: addMeal() prevents duplicates
                $order->addMeal($randomMeal);
            }

            // Track order for saving
            $manager->persist($order);
        }

        // ===== STEP 5: Save Everything to Database =====

        // Execute all INSERT queries at once
        // This is more efficient than saving each entity individually
        $manager->flush();
    }

    /**
     * Generate Random Past Date
     *
     * Creates a DateTime object representing a random date in the past.
     * Used to give orders realistic creation dates.
     *
     * How it works:
     * 1. Get current timestamp (seconds since Unix epoch)
     * 2. Subtract random number of seconds (0 to daysAgo * 86400)
     * 3. Create DateTime from that timestamp
     *
     * Example:
     * - Current time: Jan 15, 2024
     * - daysAgo: 365
     * - Random result: Mar 8, 2023 (or any date in past year)
     *
     * @param int $daysAgo Maximum days in the past (default: 365 = 1 year)
     * @return DateTime Random past date as DateTime object
     */
    function randomPastDate(int $daysAgo = 365): DateTime
    {
        // Get current Unix timestamp (seconds since Jan 1, 1970)
        $currentTime = time();

        // Calculate random number of seconds to subtract
        // 86400 = seconds in a day (60 * 60 * 24)
        // mt_rand(0, daysAgo * 86400) = random seconds within specified range
        $randomSecondsAgo = mt_rand(0, $daysAgo * 86400);

        // Calculate past timestamp
        $timestamp = $currentTime - $randomSecondsAgo;

        // Create new DateTime object
        $date = new DateTime();

        // Set it to the calculated timestamp
        $date->setTimestamp($timestamp);

        return $date;
    }
}
