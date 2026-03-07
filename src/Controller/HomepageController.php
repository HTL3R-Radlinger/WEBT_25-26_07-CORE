<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomepageController extends AbstractController
{
    // root controller
    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(): Response
    {
//        return new Response(
//            <<<EOF
//                <html>
//                    <body>
//                        <p>Hello World!</p>
//                    </body>
//                </html>
//            EOF
//        );
//
//        Set custom header:
//        $response = new Response();
//        $response->headers->set('X-Custom-Header', 'value');
//        return $response;

        return $this->render('home.html.twig');
    }
}

// _GET: ?hello=...
// public function index(Request $request): Response
// {
//     $request->query->get('hello')
//     ...
// }
