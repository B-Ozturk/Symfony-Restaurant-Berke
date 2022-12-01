<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route('/user', name: 'user')]
class ReserverenController extends AbstractController
{
    #[Route('/reserveren', name: '_reserveren')]
    public function index(Environment $twig, Request $request,EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $reservation->setUser($this->getUser());
            $reservation->setTimestamp(new \DateTimeImmutable());
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('user_reserveren_complete');
        }

        return new Response($twig->render('reserveren/index.html.twig', ['reservation_form' => $form->createView()]));
    }
}