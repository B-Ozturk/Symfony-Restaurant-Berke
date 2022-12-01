<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/', name: 'admin_')]
class ShowUserController extends AbstractController
{
    #[Route('/user/profile/{id}', name: 'show_user')]
    public function index(): Response
    {
        return $this->render('show_user/showProfile.html.twig', [
            'controller_name' => 'ShowUserController',
        ]);
    }

    #[Route('member/profile/{id}/reservations', name: 'member_reservations')]
    public function showReservations($id, ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findBy(['user' => $id],['day' => 'DESC']);

        return $this->render('show_user/showReservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('member/profile/{id}/orders', name: 'member_orders')]
    public function showOrders($id, OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy(['user' => $id]);

        return $this->render('show_user/showOrders.html.twig', [
            'orders' => $orders,
        ]);
    }
}