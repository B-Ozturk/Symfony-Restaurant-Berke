<?php

namespace App\Controller;

use App\Entity\OrderItem;
use App\Form\CartType;
use App\Form\PaymentFormType;
use App\Manager\CartManager;
use App\Repository\CouponRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user_')]
class CartController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/cart', name: 'cart')]
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

    #[Route('/order/payment', name:'order_payment')]
    public function userOrderPayment(CartManager $cartManager, OrderItemRepository $orderItemRepository , CouponRepository $couponRepository,Request $request, EntityManagerInterface $entityManager): Response
    {
        // Discount coupons are being declared here
        $actieCodes = $couponRepository->findAll();

        // Form is being declared here
        $form = $this->createForm(PaymentFormType::class);
        $form->handleRequest($request);

        // Cart is being declared here
        $cart = $cartManager->getCurrentCart();
        $items = $orderItemRepository->findBy(['orderRef' => $cart]);
        $orderItemQuantity = array_map(function ($o){ return $o->getQuantity(); }, $items);

        // Total price is being declared here
        $totalPrice = $cart->getTotalPrice();
        $finalPrice = $totalPrice;

        if ($form->isSubmitted() && $form->isValid()) {
            $inputCode = $form->get('couponCode')->getData();

            if ($cart->isDiscount() === false){
                foreach ($actieCodes as $actieCode){
                    if ($actieCode->getCode() == $inputCode){
                        $discount = (100 - $actieCode->getDiscount()) / 100;
                        $finalPrice = $totalPrice * $discount;
                        $cart->setTotalPrice($finalPrice);
                        $cart->setDiscount(true);
                        $entityManager->flush();
                    }
                }
            } elseif ($cart->isDiscount() === true){
                $this->addFlash('warning', 'Je kan maar 1x korting toepassen!');
                return $this->redirectToRoute('user_order_payment');
            }
        }else{
            $finalPrice = $totalPrice;
        }

        return $this->render('bestellen/index.html.twig', [
            'finalPrice' => $finalPrice ,'order' => $items, 'amount_array' => $orderItemQuantity, 'form' => $form->createView()
        ]);
    }

    #[Route('/ordercomplete', name:'order_complete')]
    public function userOrderComplete(): Response
    {
        $this->addFlash('success', 'Bestelling is succesvol geplaatst!');
        return $this->redirectToRoute('user_profile');
    }
}