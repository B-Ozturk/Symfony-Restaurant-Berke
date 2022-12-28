<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Entity\DiscountSeason;
use App\Repository\DiscountSeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

class CouponController extends AbstractController
{
    #[Route('/coupon', name: 'create_coupon')]
    public function index(EntityManagerInterface $entityManager, DiscountSeasonRepository $discountSeasonRepository): Response
    {
        $discountSeasons = $discountSeasonRepository->findAll();
        $curDate = date("Y-m-d");

        foreach ($discountSeasons as $discountSeason){
            if ($discountSeason = $curDate){
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
            }else{
                echo "No coupon created";
            }
        }

        return $this->render('coupon/index.html.twig', [
            'controller_name' => 'CouponController',
        ]);
    }

    #[Route('/deletecoupon', name: 'delete_coupon')]
    public function notIndex(EntityManagerInterface $entityManager, DiscountSeasonRepository $discountSeasonRepository): Response
    {
        $dates = $discountSeasonRepository->findAll();

        foreach ($dates as $date){

//            $day = $date->getDate();

            $day = date("Y/m/d", strtotime($date->getDate()));

            date_sub($day,date_interval_create_from_date_string("7 days"));
            echo date_format($date,"Y-m-d");
        }

        return $this->render('coupon/index.html.twig', [
            'controller_name' => 'CouponController',
        ]);
    }
}
