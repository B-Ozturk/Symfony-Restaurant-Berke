<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Repository\CouponsRepository;
use App\Repository\DiscountSeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;

class CouponController extends AbstractController
{
    #[Route('/coupon', name: 'create_coupon')]
    public function index(EntityManagerInterface $entityManager, DiscountSeasonRepository $discountSeasonRepository): Response
    {
        $discountSeasons = $discountSeasonRepository->findAll();
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

                $entityManager->persist($coupon);
                $entityManager->flush();

                echo "Coupon created!" . "<br>";
                dd($coupon);
            }
        }

        return $this->render('coupon/index.html.twig', [
            'controller_name' => 'CouponController',
        ]);
    }

    #[Route('/deletecoupon', name: 'delete_coupon')]
    public function notIndex(EntityManagerInterface $entityManagerInterface,
                             DiscountSeasonRepository $discountSeasonRepository,
                             CouponsRepository $couponsRepository, Connection $connection,
                            ): Response
    {

        $dates = $discountSeasonRepository->findAll();

        foreach ($dates as $date){
            $discountDate = $date->getDate();

            $today = new \DateTime();

            $checkDate = date_sub($today,date_interval_create_from_date_string("7 days"));

            $formattedcheckDate = date_format($checkDate,"Y-m-d");
            $formatteddiscountDate = date_format($discountDate, "Y-m-d");


            if($formattedcheckDate >= $formatteddiscountDate) {
                $coupons = $couponsRepository->createQueryBuilder('c')
                    ->andWhere('c.created_at LIKE :date')
                    ->setParameter('date', "%$formatteddiscountDate%")
                    ->getQuery()
                ->getResult();

                foreach ($coupons as $coupon){
                    var_dump($coupon);
                    echo "<br><br>";

                    $entityManagerInterface->remove($coupon);
                    $entityManagerInterface->flush();
                    echo "VERWIJDERD<br><br><br>";
                }

            } else {
                echo "NIKS TE VERWIJDEREN";echo  "<br>";
            }
        }

        return $this->render('coupon/index.html.twig', [
            'controller_name' => 'CouponController',
        ]);
    }
}
