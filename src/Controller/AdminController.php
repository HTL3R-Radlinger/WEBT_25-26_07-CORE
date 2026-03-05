<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Form\MealType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/meals/new', name: 'meal_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $meal = new Meal();
        $form = $this->createForm(MealType::class, $meal);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($meal);
            $em->flush();

            $this->addFlash('success', 'Meal added successfully!');
            return $this->redirectToRoute('data_meals');
        }

        return $this->render('admin/meal_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
