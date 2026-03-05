<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Form\MealType;
use App\Repository\MealRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_page', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/meals/add', name: 'meal_add')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $meal = new Meal();
        $form = $this->createForm(MealType::class, $meal);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($meal);
            $em->flush();

            $this->addFlash('success', 'Meal added successfully!');
            return $this->redirectToRoute('data_meals_list');
        }

        return $this->render('admin/meal_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/meals/edit', name: 'meal_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        return $this->render('admin/meal_edit.html.twig');
    }

    #[Route('/meals/delete', name: 'meal_delete')]
    public function delete(MealRepository $mealRepository): Response
    {
        return $this->render('admin/meal_delete.html.twig', [
            'meals' => $mealRepository->findAll(),
        ]);
    }

    #[Route('/admin/meals/{id}/delete', name: 'meal_delete_by_id', methods: ['POST'])]
    public function delete_meal(Meal $meal, EntityManagerInterface $em): Response
    {
        $em->remove($meal);
        $em->flush();

        $this->addFlash('success', 'Meal deleted successfully!');

        return $this->redirectToRoute('meal_delete');
    }
}
