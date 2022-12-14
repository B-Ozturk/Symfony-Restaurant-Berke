<?php

namespace App\Controller;

use App\Entity\MenuReview;
use App\Form\MenuReviewType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route('user', name: 'user_')]
class MenuReviewController extends AbstractController
{
    #[Route('/product/{id}/review', name: 'product_review')]
    public function userProductReview($id, MenuRepository $menuRepository,Environment $twig, Request $request, EntityManagerInterface $entityManager): Response
    {
        $menuReview = new MenuReview();

        $form = $this->createForm(MenuReviewType::class, $menuReview);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $menuReview->setUser($this->getUser());
            $menuReview->setDate(new \DateTimeImmutable());
            $menuReview->setMenu($id);
            $menuReview->setMessage($form->getData('message'));
            $menuReview->setStars($form->getData('stars'));

            $entityManager->persist($menuReview);
            $entityManager->flush();

            $this->addFlash('success', 'Review is succesvol geplaast!');
            return $this->redirectToRoute('user_product_review');
        }
        return new Response($twig->render('menu_review/menuReview.html.twig', ['menuReview' => $form->createView()]));
    }
}
