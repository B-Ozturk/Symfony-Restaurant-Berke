<?php

namespace App\Controller;

use App\Entity\DiscountSeason;
use App\Repository\DiscountSeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function makeDiscountSeason(DiscountSeasonRepository $discountSeasonRepository, EntityManagerInterface $entityManager): Response
    {
        $discountSeason = new DiscountSeason();

        $form = 'form';
        $formDate = $form->get('date')->getData();

        $deleteDate = date_add($formDate,date_interval_create_from_date_string("7 days"));


        $discountSeason->setDate($formDate);
        $discountSeason->setDeleteDate($deleteDate);

        $entityManager->persist($discountSeason);
        $entityManager->flush();

        return $this->render('coupon/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
