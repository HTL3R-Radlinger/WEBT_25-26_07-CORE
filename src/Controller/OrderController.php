<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * OrderController - Manages order listing and status updates
 *
 * This controller handles:
 * - Displaying orders with filtering and pagination
 * - Updating order status (pending -> confirmed -> preparing -> ready -> delivered)
 *
 * All routes are prefixed with /orders
 */
#[Route('/orders')]
final class OrderController extends AbstractController
{
    /**
     * List Orders with Filtering and Pagination
     *
     * Displays a paginated list of orders with multiple filtering options:
     * - Filter by status (pending, confirmed, preparing, ready, delivered, cancelled)
     * - Filter by buyer (user who placed the order)
     * - Sort by date, price, or status
     * - Paginate results (3 orders per page)
     *
     * Also displays status statistics (count of orders in each status)
     *
     * URL Examples:
     * - /orders?status=pending           -> Show only pending orders
     * - /orders?buyer=5                  -> Show orders from buyer with ID 5
     * - /orders?sortBy=price&order=DESC  -> Sort by price descending
     * - /orders?page=2                   -> Show page 2
     *
     * @param Request $request HTTP request containing query parameters
     * @param OrderRepository $orderRepository Repository for order database queries
     * @return Response Renders the order list template
     */
    #[Route('/', name: 'order_list')]
    public function list(Request $request, OrderRepository $orderRepository): Response
    {
        // Get filter parameters from URL query string

        // Filter by status (e.g., ?status=pending)
        $status = $request->query->get('status');

        // Filter by buyer ID (e.g., ?buyer=5)
        // Convert to integer, or null if not provided
        $buyerId = $request->query->get('buyer') ? (int)$request->query->get('buyer') : null;

        // Get sorting preferences
        $sortBy = $request->query->get('sortBy', 'date');  // Default: sort by date
        $order = $request->query->get('order', 'DESC');     // Default: newest first

        // Get page number, ensure minimum value is 1
        $page = max((int)$request->query->get('page', 1), 1);

        // Set how many orders to show per page
        $limit = 3;

        // Query database with all filters and pagination
        // Returns a Paginator object containing the results
        $paginator = $orderRepository->findByStatus($status, $buyerId, $sortBy, $order, $page, $limit);

        // Get statistics: how many orders are in each status
        // Returns array like: [['status' => 'pending', 'total' => 5], ...]
        $statusCounts = $orderRepository->countByStatus();

        // Render template with all data needed for display
        return $this->render('order/list.html.twig', [
            'orders' => $paginator,                          // Paginated order results
            'statusCounts' => $statusCounts,                 // Order counts per status
            'currentPage' => $page,                          // Current page number
            'totalPages' => ceil(count($paginator) / $limit), // Calculate total pages
            'currentStatus' => $status,                      // Currently filtered status
        ]);
    }

    /**
     * Update Order Status
     *
     * Changes the status of an order (e.g., from 'pending' to 'confirmed')
     *
     * Status flow:
     * pending -> confirmed -> preparing -> ready -> delivered
     * (can also be set to 'cancelled' at any point)
     *
     * Security: Only POST method allowed to prevent accidental status changes
     * via GET links or browser refresh
     *
     * Error handling: If invalid status is provided, shows error message
     *
     * @param Order $order The order to update (auto-loaded by Symfony)
     * @param Request $request HTTP request containing the new status
     * @param EntityManagerInterface $em Entity manager for database operations
     * @return Response Redirects back to order list with success/error message
     */
    #[Route('/{id}/status', name: 'order_update_status', methods: ['POST'])]
    public function updateStatus(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        // Get the new status from POST data
        // Typically sent from a form: <input name="status" value="confirmed">
        $newStatus = $request->request->get('status');

        // setStatus() validates the status value
        // Throws InvalidArgumentException if status is invalid
        $order->setStatus($newStatus);

        // Save changes to database
        $em->flush();

        // Redirect back to the order list page
        // User will see either success or error message
        return $this->redirectToRoute('order_list');
    }
}
