<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Entity\DiscountSeason;
use App\Repository\DiscountSeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CouponController extends AbstractController
{
    #[Route('/code', name: 'app_coupon')]
    public function index(EntityManagerInterface $entityManager, DiscountSeasonRepository $discountSeason): Response
    {
        $discountSeasons = $discountSeason->findAll();
        $curDate = date("Y-m-d");

        foreach ($discountSeasons as $season){
            if ($season === $curDate){
                var_dump("YES");
            }
        }

        return $this->render('coupon/index.html.twig', [
            'controller_name' => 'CouponController',
        ]);
    }

    #[Route('/coupon2', name: 'app_coupon2')]
    public function notIndex(EntityManagerInterface $entityManager): Response
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


        return $this->render('coupon/index.html.twig', [
            'controller_name' => 'CouponController',
        ]);
    }
}
