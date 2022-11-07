<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\OrderItem;
use App\Form\MenuItemType;
use App\Repository\MenuRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        $menus = $MenuRepository->findBy([], ['category' => 'ASC']);

        return $this->render('admin/menu.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/add_item', name: 'add_item')]
    public function addItem(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menu = new Menu();

        $form = $this->createForm(MenuItemType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $menu->setName($form->get('name')->getData());
            $menu->setDescription($form->get('description')->getData());

            $picture = $form->get('picture')->getData();
            if ($picture){
                $newFileName = uniqid() . '.' . $picture->guessExtension();

                try {
                    $picture->move(
                        $this->getParameter('kernel.project_dir') . '/public/img/menu', $newFileName
                    );
                } catch (FileException $e){
                    return new Response($e->getMessage());
                }

                $menu->setPicture($newFileName);
            }


            $entityManager->persist($menu);
            $entityManager->flush();
        }

        return $this->render('admin/AddItem.html.twig', [
            'addItemForm' => $form->createView()
        ]);
    }

    #[Route('/orders', name: 'orders')]
    public function showOrders(OrderRepository $OrderRepository): Response
    {
        $carts = $OrderRepository->findAll();

        return $this->render('admin/orders.html.twig', [
            'carts' => $carts
        ]);
    }

    #[Route('/order/{id}', name: 'order', methods:['GET', 'HEAD'])]
    public function showOrder(int $id, OrderItemRepository $OrderItemRepository, MenuRepository $MenuRepository): Response
    {
        $items = $OrderItemRepository->findBy(['orderRef' => $id]);

//        $items = $OrderItemRepository->findBy(['orderRef' => $id]);
//        $items->id;
//        $cart_items = $MenuRepository->findBy(['id' => $items->id]);

        return $this->render('admin/order.html.twig', [
            'items' => $items
        ]);
    }
}
