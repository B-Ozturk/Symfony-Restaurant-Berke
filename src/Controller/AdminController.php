<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Reservation;
use App\Form\MenuItemType;
use App\Repository\MenuRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ReservationRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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

    #[Route('/members', name: 'members')]
    public function showMembers(UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $users = $userRepository->findUsersOnly();

        return $this->render('admin/members.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/members/delete/{id}', name: 'delete_member')]
    public function deleteMember(
        $id, EntityManagerInterface $entityManager,
        UserRepository $userRepository, ReviewRepository $reviewRepository,
        OrderRepository $orderRepository, ReservationRepository $reservationRepository
    ): Response
    {
        $member = $userRepository->find($id);
        $reviews = $reviewRepository->findBy(['user' => $id]);
        $orders = $orderRepository->findBy(['user' => $id]);
        $reservations = $reservationRepository->findBy(['user' => $id]);

        if ($reviews){
            foreach ($reviews as $rev){
                $entityManager->remove($rev);
            }
        }
        if ($orders){
            foreach ($orders as $order){
                $entityManager->remove($order);
            }
        }
        if ($reservations){
            foreach ($reservations as $reservation){
                $entityManager->remove($reservation);
            }
        }
        if ($member){
            $entityManager->remove($member);
        }

        $entityManager->flush();

        $this->addFlash('success', $member->getName() . ' is succesvol verwijderd!');

        return $this->redirectToRoute('admin_members');
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

            $this->addFlash('success', 'Item is succesvol toegevoegd aan het menu!');
            return $this->redirectToRoute('admin_menu');
        }

        return $this->render('admin/add_item.html.twig', [
            'addItemForm' => $form->createView()
        ]);
    }

    #[Route('/menu/edit/{id}', name: 'edit_item')]
    public function edit_item($id, MenuRepository $menuRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $menu_item = $menuRepository->find($id);
        $form = $this->createForm(MenuItemType::class, $menu_item);

        $form->handleRequest($request);
        $picture = $form->get('picture')->getData();

        if ($form->isSubmitted() && $form->isValid()){
            if ($picture){
                if ($menu_item->getPicture() !== null){
                    if (file_exists($this->getParameter('kernel.project_dir') . $menu_item->getPicture())){
                        $this->getParameter('kernel.project_dir') . $menu_item->getPicture();
                    }
                    $newFileName = uniqid() . '.' . $picture->guessExtension();

                    try {
                        $picture->move(
                            $this->getParameter('kernel.project_dir') . '/public/img/menu', $newFileName
                        );
                    } catch (FileException $e){
                        return new Response($e->getMessage());
                    }

                    $menu_item->setPicture($newFileName);

                    $entityManager->flush();

                    $this->addFlash('success', $menu_item->getName() .' is succesvol aangepast!');
                    return $this->redirectToRoute('admin_menu');
                }
            }else{
                $menu_item->setName($form->get('name')->getData());
                $menu_item->setDescription($form->get('description')->getData());
                $menu_item->setPrice($form->get('price')->getData());
                $menu_item->setCategory($form->get('category')->getData());

                $entityManager->flush();

                $this->addFlash('success', $menu_item->getName() .' is succesvol aangepast!');
                return $this->redirectToRoute('admin_menu');
            }
        }

        return $this->render('admin/edit_item.html.twig', [
        'menu_item' => $menu_item, 'form' => $form->createView()
        ]);
    }

    #[Route('/menu/delete/{id}', name: 'delete_item')]
    public function delete_item($id, MenuRepository $menuRepository, EntityManagerInterface $entityManager): Response
    {
        $menu_item = $menuRepository->find($id);

        $entityManager->remove($menu_item);
        $entityManager->flush();

        $this->addFlash('success', $menu_item->getName() .' is succesvol verwijderd van het menu!');
        return $this->redirectToRoute('admin_menu');
    }

    #[Route('/reservations', name: 'reservations')]
    public function showReservations(ReservationRepository $reservationRepository): Response
    {
        $todaysDate = new \DateTime();

        $todaysReservations = $reservationRepository->findBy(['day' => $todaysDate], ['time' => 'ASC']);

        return $this->render('admin/reservations.html.twig', [
            'todaysReservations' => $todaysReservations,
        ]);
    }

    #[Route('/reservation/complete/{id}', name: 'reservation_complete')]
    public function completeReservations($id ,ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        $reservation = $reservationRepository->findOneBy(['id' => $id]);

        $entityManager->remove($reservation);
        $entityManager->flush();

        $this->addFlash('success', "Reservering #" . $id . " is succesvol afgerond!");
        return $this->redirectToRoute('admin_reservations');
    }

    #[Route('/orders', name: 'orders')]
    public function showOrders(OrderRepository $OrderRepository): Response
    {
        $carts = $OrderRepository->findBy([], ['id' => 'ASC']);

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

    #[Route('/order/complete/{id}', name: 'complete_order', methods:['GET', 'DELETE'])]
    public function completeOrder($id, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->find($id);

        $entityManager->remove($order);
        $entityManager->flush();

        $this->addFlash('success', "Bestelling " . $id . " is succesvol afgerond!");
        return $this->redirectToRoute('admin_orders');
    }

    #[Route('/user/profile/{id}', name: 'member_profile')]
    public function showMemberProfile($id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        return $this->render('show_admin/showProfile.html.twig', [
            'user' => $user,
        ]);
    }
}