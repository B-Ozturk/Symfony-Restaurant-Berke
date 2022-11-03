<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(): Response
    {
        return $this->render('admin/home.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/menu', name: 'menu')]
    public function menu(MenuRepository $MenuRepository): Response
    {
        $menus = $MenuRepository->findAll();

        return $this->render('admin/menu.html.twig', [
            'menus' => $menus,
        ]);
    }
}
