<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user')]
class ProductController extends AbstractController
{
    #[Route('/product/{id}', name: '_productdetail')]
    public function index(Menu $menu, Request $request, CartManager $CartManager): Response
    {
        $form = $this->createForm(AddToCartType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $item->setProduct($menu);

            $cart = $CartManager->getCurrentCart();
            $cart
                ->addItem($item)
                ->setUpdatedAt(new \DateTime());

            $CartManager->save($cart);

            return $this->redirectToRoute('user_productdetail', ['id' => $menu->getId()]);
        }

        return $this->render('product/index.html.twig', [
            'menu' => $menu,
            'form' => $form->createView()
        ]);
    }
}
