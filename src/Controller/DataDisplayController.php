<?php

namespace App\Controller;

use App\Repository\AllergenRepository;
use App\Repository\MealRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DataDisplayController extends AbstractController
{
    #[Route('/meals/list', name: 'data_meals_list')]
    public function list(Request $request, MealRepository $mealRepository, AllergenRepository $allergenRepository): Response
    {
        $name = $request->query->get('name'); // ?name=XYZ
        $excludeAllergens = $request->query->all('excludeAllergens'); // ?excludeAllergens[]=1&excludeAllergens[]=2
        $sortBy = $request->query->get('sortBy', 'name'); // ?sortBy=XYZ
        $order = $request->query->get('order', 'ASC'); // ?order=XYZ
        $page = max((int)$request->query->get('page', 1), 1); // ?page=123

        $limit = 3; // items per page
        $paginator = $mealRepository->findMeals($name, $excludeAllergens, $sortBy, $order, $page, $limit);
        $allAllergens = $allergenRepository->findAll();

        return $this->render('meals/list.html.twig', [
            'meals' => $paginator,
            'allergens' => $allAllergens,
            'currentPage' => $page,
            'totalPages' => ceil(count($paginator) / $limit),
            'excludeAllergens' => $excludeAllergens,
        ]);
    }
}
