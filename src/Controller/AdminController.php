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

/**
 * AdminController - Handles all administrative functions for the meal ordering system
 *
 * This controller is responsible for:
 * - Displaying the admin dashboard
 * - Creating new meals
 * - Editing existing meals
 * - Deleting meals
 *
 * All routes are prefixed with /admin
 */
#[Route('/admin')]
final class AdminController extends AbstractController
{
    /**
     * Admin Dashboard Homepage
     *
     * Displays the main admin page with options to manage meals
     *
     * @return Response Renders the admin/index.html.twig template
     */
    #[Route('/', name: 'admin_page', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * Add New Meal
     *
     * Handles both displaying the form and processing the submission
     *
     * Process flow:
     * 1. Create a new empty Meal entity
     * 2. Build a form based on MealType
     * 3. If form is submitted and valid, save to database
     * 4. Otherwise, display the form
     *
     * @param Request $request HTTP request object containing form data
     * @param EntityManagerInterface $em Doctrine entity manager for database operations
     * @return Response Either redirects to meal list or displays the form
     */
    #[Route('/meals/add', name: 'meal_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        // Create a new, empty Meal object
        $meal = new Meal();

        // Create the form using the MealType form class
        // This will generate input fields for name, nutritionalInfo, and allergens
        $form = $this->createForm(MealType::class, $meal);

        // Process the HTTP request and populate the form with submitted data
        $form->handleRequest($request);

        // Check if form was submitted AND all validation rules passed
        if ($form->isSubmitted() && $form->isValid()) {
            // Tell Doctrine to track this new meal entity
            $em->persist($meal);

            // Execute the INSERT query to save to database
            $em->flush();

            // Redirect to the meals list page after successful creation
            return $this->redirectToRoute('data_meals_list');
        }

        // If form not submitted or has errors, render the add form template
        // createView() converts the form object to a renderable format
        return $this->render('admin/meal/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Meal Edit Selection Page
     *
     * Displays a list of all meals so the admin can choose which one to edit
     *
     * @param MealRepository $mealRepository Repository for querying meal data
     * @return Response Renders template with list of all meals
     */
    #[Route('/meals/select/edit', name: 'meal_edit')]
    public function edit_selection(MealRepository $mealRepository): Response
    {
        // Retrieve all meals from database using the repository
        return $this->render('admin/meal/select/edit.html.twig', [
            'meals' => $mealRepository->findAll()
        ]);
    }

    /**
     * Edit Specific Meal by ID
     *
     * Handles editing an existing meal. The {id} in the route is automatically
     * converted to a Meal object by Symfony's ParamConverter
     *
     * Process flow:
     * 1. Meal object is automatically loaded from database by ID
     * 2. Form is pre-populated with existing meal data
     * 3. If form is submitted and valid, changes are saved
     * 4. Otherwise, form is displayed with current values
     *
     * @param Meal $meal The meal entity to edit (auto-loaded by Symfony)
     * @param Request $request HTTP request containing form data
     * @param EntityManagerInterface $em Entity manager for database operations
     * @param MealRepository $mealRepository Not currently used, could be removed
     * @return Response Either redirects after save or displays the edit form
     */
    #[Route('/meals/{id}/edit', name: 'meal_edit_by_id')]
    public function edit(Meal $meal, Request $request, EntityManagerInterface $em, MealRepository $mealRepository): Response
    {
        // Create form pre-filled with existing meal data
        $form = $this->createForm(MealType::class, $meal);

        // Process the submitted form data
        $form->handleRequest($request);

        // Check if form was submitted and passed validation
        if ($form->isSubmitted() && $form->isValid()) {
            // No need to call persist() for existing entities
            // flush() alone will UPDATE the database record
            $em->flush();

            // Redirect back to the meals list
            return $this->redirectToRoute('data_meals_list');
        }

        // Display the edit form with current meal data
        return $this->render('admin/meal/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Meal Delete Selection Page
     *
     * Displays a list of all meals so the admin can choose which one to delete
     *
     * @param MealRepository $mealRepository Repository for querying meal data
     * @return Response Renders template with list of all meals
     */
    #[Route('/meals/select/delete', name: 'meal_delete')]
    public function delete_selection(MealRepository $mealRepository): Response
    {
        return $this->render('admin/meal/select/delete.html.twig', [
            'meals' => $mealRepository->findAll(),
        ]);
    }

    /**
     * Delete Specific Meal by ID
     *
     * Removes a meal from the database. Only accepts POST requests for security
     * (prevents accidental deletion via GET links)
     *
     * @param Meal $meal The meal to delete (auto-loaded by Symfony)
     * @param EntityManagerInterface $em Entity manager for database operations
     * @return Response Redirects back to delete selection page
     */
    #[Route('/meals/{id}/delete', name: 'meal_delete_by_id', methods: ['POST'])]
    public function delete(Meal $meal, EntityManagerInterface $em): Response
    {
        // Mark the meal entity for deletion
        $em->remove($meal);

        // Execute the DELETE query
        $em->flush();

        // Redirect back to the delete selection page
        return $this->redirectToRoute('meal_delete');
    }
}
