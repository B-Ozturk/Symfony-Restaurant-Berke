<?php

namespace App\Controller;

use App\Form\CartType;
use App\Manager\CartManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user')]
class CartController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/cart', name: '_cart')]
    public function index(CartManager $cartManager, Request $request): Response
    {
        $cart = $cartManager->getCurrentCart();
//        $cartManager->setUser($this->getUser());

        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cart->setUpdatedAt(new \DateTime());
            dd($cart);
            $cart->setUser($this->getUser());

            $cart->setName($this->getUser()->getName());



            $this->entityManager->persist($cart);
            $this->entityManager->flush();
//            $cartManager->save($cart);

            return $this->redirectToRoute('cart');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView()
        ]);
    }
}