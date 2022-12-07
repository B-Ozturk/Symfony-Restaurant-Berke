<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route('/user', name: 'user')]
class ReviewController extends AbstractController
{
    #[Route('/review', name: '_review')]
    public function show(Environment $twig, Request $request,EntityManagerInterface $entityManager): Response
    {
        $review = new Review();

        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $review->setUser($this->getUser());
            $review->setDate(new \DateTimeImmutable());
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Review is succesvol geplaatst!');
            return $this->redirectToRoute('user_profile');
        }

        return new Response($twig->render('user/review.html.twig', ['review_form' => $form->createView()]));
    }
}
