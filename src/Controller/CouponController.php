<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CouponController extends AbstractController
{
    #[Route('/coupon', name: 'app_coupon')]
    public function index( EntityManagerInterface $entityManager): Response
    {

        return $this->render('coupon/showProfile.html.twig', [
            'controller_name' => 'CouponController',
        ]);
    }

    #[Route('/coupon', name: 'app_coupon')]
    public function notIndex( EntityManagerInterface $entityManager): Response
    {
        $coupon = new Coupon();

        $length = 6;
        $word = array_merge(range('A', 'Z'));
        shuffle($word);
        $discount = rand(5, 75);
        $code = substr(implode($word), 0, $length) . strval($discount);

        $coupon->setCode($code);
        $coupon->setDiscount($discount);
        $coupon->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($coupon);
        $entityManager->flush();


        return $this->render('coupon/showProfile.html.twig', [
            'controller_name' => 'CouponController',
        ]);
    }
}
