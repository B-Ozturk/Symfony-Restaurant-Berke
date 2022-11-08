<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Order;
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

            return $this->redirectToRoute('admin_add_item_complete');
        }

        return $this->render('admin/AddItem.html.twig', [
            'addItemForm' => $form->createView()
        ]);
    }

    #[Route('/add_item_complete', name: 'add_item_complete')]
    public function add_item_complete(): Response
    {
        return $this->render('admin/action_complete.html.twig', [
            'text' => 'Item successvol toegevoegd aan het menu',
        ]);
    }

    #[Route('/orders', name: 'orders')]
    public function showOrders(OrderRepository $OrderRepository): Response
    {
        $carts = $OrderRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/orders.html.twig', [
            'carts' => $carts
        ]);
    }

    #[Route('/order/{id}', name: 'order', methods:['GET', 'HEAD'])]
    public function showOrder(Order $order, OrderItemRepository $orderItemRepository): Response
    {
        $orderItem = $orderItemRepository->findBy(['orderRef' => $order]);

        $orderItemQuantity = array_map(function ($o){ return $o->getQuantity(); }, $orderItem);

        return $this->render('admin/order.html.twig', [
            'order' => $order, 'amount_array' => $orderItemQuantity
        ]);
    }
}
