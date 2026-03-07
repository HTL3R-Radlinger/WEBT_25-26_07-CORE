<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/orders')]
final class OrderController extends AbstractController
{
    #[Route('/', name: 'order_list')]
    public function list(Request $request, OrderRepository $orderRepository): Response
    {
        $status = $request->query->get('status');
        $buyerId = $request->query->get('buyer') ? (int)$request->query->get('buyer') : null;
        $sortBy = $request->query->get('sortBy', 'date');
        $order = $request->query->get('order', 'DESC');
        $page = max((int)$request->query->get('page', 1), 1);
        $limit = 3;

        $paginator = $orderRepository->findByStatus($status, $buyerId, $sortBy, $order, $page, $limit);
        $statusCounts = $orderRepository->countByStatus();

        return $this->render('order/list.html.twig', [
            'orders' => $paginator,
            'statusCounts' => $statusCounts,
            'currentPage' => $page,
            'totalPages' => ceil(count($paginator) / $limit),
            'currentStatus' => $status,
        ]);
    }

    #[Route('/{id}/status', name: 'order_update_status', methods: ['POST'])]
    public function updateStatus(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        $newStatus = $request->request->get('status');
        try {
            $order->setStatus($newStatus);
            $em->flush();
            $this->addFlash('success', 'Order status updated!');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('order_list');
    }
}
