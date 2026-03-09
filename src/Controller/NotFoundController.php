<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * NotFoundController - Handles 404 Error Pages
 *
 * This controller provides a custom 404 (Not Found) error page
 * for the application
 */
final class NotFoundController extends AbstractController
{
    /**
     * Custom 404 Not Found Page
     *
     * This method renders a custom error page when users navigate to
     * /notfound or when explicitly redirected here.
     *
     * Note: This is a custom route for 404 pages. For automatic 404 handling
     * throughout the site, you would typically configure error templates in
     * templates/bundles/TwigBundle/Exception/error404.html.twig
     *
     * The third parameter in render() is a Response object with status 404,
     * which tells the browser this is an error page (important for SEO)
     *
     * @return Response Renders error404.html.twig with HTTP status 404
     */
    #[Route('/notfound', name: 'notfound_page', methods: ['GET'])]
    public function index(): Response
    {
        // Render the 404 template with HTTP status code 404
        // The empty array [] is for template variables (none needed here)
        // new Response('', 404) creates a response with 404 status code
        return $this->render('error404.html.twig', [], new Response('', 404));
    }
}
