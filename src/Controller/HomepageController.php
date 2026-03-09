<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HomepageController - Handles the main homepage of the application
 *
 * This is the entry point for users visiting the root URL (/)
 */
final class HomepageController extends AbstractController
{
    /**
     * Homepage Route - Root Controller
     *
     * This method handles requests to the root URL (/) of the application
     *
     * Below are commented-out examples showing different ways to create responses:
     *
     * Example 1: Direct HTML Response (without template)
     * You could return raw HTML directly:
     *   return new Response('<html><body><p>Hello World!</p></body></html>');
     *
     * Example 2: Custom Headers
     * You can set custom HTTP headers:
     *   $response = new Response();
     *   $response->headers->set('X-Custom-Header', 'value');
     *   return $response;
     *
     * Current implementation: Uses Twig template system for better separation
     * of logic and presentation
     *
     * @return Response Renders the home.html.twig template
     */
    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(): Response
    {
        // Example 1: Direct HTML response (commented out)
        // This creates a Response object with raw HTML
        // Useful for simple responses but not maintainable for complex pages
//        return new Response(
//            <<<EOF
//                <html>
//                    <body>
//                        <p>Hello World!</p>
//                    </body>
//                </html>
//            EOF
//        );

        // Example 2: Setting custom headers (commented out)
        // You can add custom HTTP headers to the response
        // Useful for CORS, caching, or custom API headers
//        $response = new Response();
//        $response->headers->set('X-Custom-Header', 'value');
//        return $response;

        // Current implementation: Render a Twig template
        // This is the recommended approach for rendering HTML pages
        // Templates are stored in templates/ directory
        return $this->render('home.html.twig');
    }
}

// Additional examples (commented out):
//
// How to access URL query parameters:
// Example URL: /?hello=world
//
// public function index(Request $request): Response
// {
//     // Get the 'hello' parameter from the query string
//     $helloValue = $request->query->get('hello');
//     // $helloValue would contain 'world'
//
//     return $this->render('home.html.twig', [
//         'message' => $helloValue
//     ]);
// }
