<?php

namespace App\Controller;

use App\Repository\MealRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DataDisplayController extends AbstractController
{
    #[Route('/meals', name: 'data_meals')]
    public function index(MealRepository $mealRepository): Response
    {

        return $this->render('data/meals.html.twig', [
            'meals' => $mealRepository->findAll(),
        ]);
    }
}
