<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\MenuItemType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

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

    #[Route('/addItem', name: 'addItem')]
    public function addItem(Request $request, EntityManagerInterface $entityManager, Environment $twig): Response
    {
        $menu = new Menu();

        $form = $this->createForm(MenuItemType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $menu->setName($form->get('name')->getData());
            $menu->setDescription($form->get('description')->getData());
        }

        return $this->render('admin/AddItem.html.twig', [
            'addItemForm' => $form->createView(),
        ]);
    }
}
