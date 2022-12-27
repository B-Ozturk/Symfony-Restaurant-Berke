<?php

namespace App\Controller;

use App\Entity\OrderItem;
use App\Form\CartType;
use App\Form\PaymentFormType;
use App\Manager\CartManager;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
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

        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cart->setUpdatedAt(new \DateTime());
            $cart->setUser($this->getUser());
            $cart->setTotalPrice($cart->getTotal());

            $this->entityManager->persist($cart);
            $this->entityManager->flush();

            return $this->redirectToRoute('user_cart');
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView()
        ]);
    }

    #[Route('/order/payment', name:'_order_payment')]
    public function userOrderComplete(CartManager $cartManager, OrderItemRepository $orderItemRepository ,Request $request): Response
    {
        // Default variables
        $actie = 'BERKE20'; // This is the discountcode
        $totalPrice = 0;

        // Form is being declared here
        $form = $this->createForm(PaymentFormType::class);
        $form->handleRequest($request);

        // Cart is being declared here
        $cart = $cartManager->getCurrentCart();
        $items = $orderItemRepository->findBy(['orderRef' => $cart]);
        $orderItemQuantity = array_map(function ($o){ return $o->getQuantity(); }, $items);

        foreach ($items as $item){
            $product = $item->getProduct();
            $totalPrice += $product->getPrice() * $item->getQuantity();
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $inputCode = $form->get('couponCode')->getData();

            if ($actie == $inputCode){
                $finalPrice = $totalPrice * 0.85;
            } else {
                $this->addFlash('warning', 'Kortingscode is niet geldig!');
                return $this->redirectToRoute('user_order_payment');
            }

        }else{
            $finalPrice = $totalPrice;
        }

        return $this->render('bestellen/index.html.twig', [
            'actie' => $actie, 'finalPrice' => $finalPrice ,'order' => $items, 'amount_array' => $orderItemQuantity, 'form' => $form->createView()
        ]);
    }
}