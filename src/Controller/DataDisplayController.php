<?php

namespace App\Controller;

use App\Repository\MealRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DataDisplayController extends AbstractController
{
    #[Route('/meals/list', name: 'data_meals_list')]
    public function list(Request $request, MealRepository $mealRepository): Response
    {
        $name = $request->query->get('name'); // ?name=XYZ
        $sortBy = $request->query->get('sortBy', 'name'); // ?sortBy=XYZ
        $order = $request->query->get('order', 'ASC'); // ?order=XYZ
        $page = max((int)$request->query->get('page', 1), 1); // ?page=123

        $limit = 3; // items per page
        $paginator = $mealRepository->findMeals($name, $sortBy, $order, $page, $limit);

        return $this->render('data/meals_list.html.twig', [
            'meals' => $paginator,
            'currentPage' => $page,
            'totalPages' => ceil(count($paginator) / $limit),
        ]);
    }
}
