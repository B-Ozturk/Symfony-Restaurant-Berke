<?php

namespace App\Service;

use App\Repository\CouponsRepository;
use App\Repository\DiscountSeasonRepository;
use Doctrine\ORM\EntityManagerInterface;

class CouponService
{
    public function __construct(private EntityManagerInterface $entityManagerInterface,private DiscountSeasonRepository $discountSeasonRepository, private CouponsRepository $couponsRepository)
    {
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