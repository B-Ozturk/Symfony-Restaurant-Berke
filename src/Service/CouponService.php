<?php

namespace App\Service;

use App\Entity\Coupon;
use App\Repository\CouponsRepository;
use App\Repository\DiscountSeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CouponService
{
    public function __construct(private EntityManagerInterface $entityManagerInterface,private DiscountSeasonRepository $discountSeasonRepository, private CouponsRepository $couponsRepository)
    {

    }

    public function makeCoupon()
    {
        $discountSeasons = $this->discountSeasonRepository->findAll();
        $curDate = date("Y-m-d");

        foreach ($discountSeasons as $discountSeason){
            if (date_format($discountSeason->getDate(), "Y-m-d") == $curDate){
                $coupon = new Coupon();

                $length = 6;
                $word = array_merge(range('A', 'Z'));
                shuffle($word);
                $discount = rand(5, 75);
                $code = substr(implode($word), 0, $length) . strval($discount);

                $coupon->setCode($code);
                $coupon->setDiscount($discount);
                $coupon->setCreatedAt(new \DateTimeImmutable());

                $this->entityManagerInterface->persist($coupon);
                $this->entityManagerInterface->flush();
            }
        }
    }

    public function checkCoupon(){
        $dates = $this->discountSeasonRepository->findAll();

        foreach ($dates as $date){
            $discountDate = $date->getDate();
            $today = new \DateTime();
            $checkDate = date_sub($today,date_interval_create_from_date_string("7 days"));

            $formattedcheckDate = date_format($checkDate,"Y-m-d");
            $formatteddiscountDate = date_format($discountDate, "Y-m-d");

            if($formattedcheckDate >= $formatteddiscountDate) {
                $coupons = $this->couponsRepository->findCouponsByDate($formatteddiscountDate);

                foreach ($coupons as $coupon){
                    $this->entityManagerInterface->remove($coupon);
                    $this->entityManagerInterface->flush();
                }
            } else {

            }
        }
    }
}