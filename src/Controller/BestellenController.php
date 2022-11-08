<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user')]
class BestellenController extends AbstractController
{
    #[Route('/bestellen', name: '_bestellen')]
    public function index(MenuRepository $MenuRepository): Response
    {
        $menus = $MenuRepository->findBy([], ['category' => 'ASC']);

        return $this->render('user/bestellen.html.twig',[
            'menus' => $menus
        ]);
    }
}
