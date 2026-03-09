<?php

namespace App\Controller;

use App\Repository\AllergenRepository;
use App\Repository\MealRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * DataDisplayController - Handles public-facing data display
 *
 * This controller is responsible for displaying filtered and paginated
 * lists of meals to end users (not admin functions)
 */
final class DataDisplayController extends AbstractController
{
    /**
     * Display Filtered and Paginated Meal List
     *
     * This is a complex method that handles multiple features:
     * - Search meals by name
     * - Filter out meals containing specific allergens
     * - Sort by different fields (name, id, allergens)
     * - Paginate results (3 items per page)
     *
     * URL parameters supported:
     * - ?name=Pizza              -> Search for meals containing "Pizza"
     * - ?excludeAllergens[]=1    -> Exclude meals with allergen ID 1
     * - ?sortBy=name&order=DESC  -> Sort by name descending
     * - ?page=2                  -> Show page 2 of results
     *
     * @param Request $request HTTP request object containing query parameters
     * @param MealRepository $mealRepository Repository for complex meal queries
     * @param AllergenRepository $allergenRepository Repository for allergen data
     * @return Response Renders the meal list template with filtered results
     */
    #[Route('/meals/list', name: 'data_meals_list')]
    public function list(Request $request, MealRepository $mealRepository, AllergenRepository $allergenRepository): Response
    {
        // Extract query parameters from URL
        // Example: /meals/list?name=Pizza
        $name = $request->query->get('name');

        // Get array of allergen IDs to exclude from results
        // Example: ?excludeAllergens[]=1&excludeAllergens[]=2
        // This will return [1, 2] as an array
        $excludeAllergens = $request->query->all('excludeAllergens');

        // Get sorting field, default to 'name' if not specified
        // Example: ?sortBy=price
        $sortBy = $request->query->get('sortBy', 'name');

        // Get sort order (ASC or DESC), default to ASC
        // Example: ?order=DESC
        $order = $request->query->get('order', 'ASC');

        // Get page number, ensure it's at least 1
        // max() prevents negative page numbers
        // Example: ?page=3
        $page = max((int)$request->query->get('page', 1), 1);

        // Set how many meals to show per page
        $limit = 3;

        // Call custom repository method that handles all the complex filtering
        // Returns a Paginator object (not a simple array)
        $paginator = $mealRepository->findMeals($name, $excludeAllergens, $sortBy, $order, $page, $limit);

        // Get all allergens for the filter checkboxes in the UI
        $allAllergens = $allergenRepository->findAll();

        // Render the template with all necessary data
        return $this->render('meals/list.html.twig', [
            'meals' => $paginator,              // Paginated meal results
            'allergens' => $allAllergens,       // All allergens for filter UI
            'currentPage' => $page,             // Current page number
            'totalPages' => ceil(count($paginator) / $limit),  // Calculate total pages
            'excludeAllergens' => $excludeAllergens,  // Currently excluded allergens
        ]);
    }
}
