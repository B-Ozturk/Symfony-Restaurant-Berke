<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Entity\DiscountSeason;
use App\Entity\Menu;
use App\Entity\Openingstijden;
use App\Entity\Order;
use App\Form\CouponType;
use App\Form\DiscountSeasonType;
use App\Form\MenuItemType;
use App\Form\OpeningstijdenType;
use App\Security\EmailVerifier;
use App\Form\RandomCouponType;
use App\Repository\MenuRepository;
use App\Repository\OpeningstijdenRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ReservationRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\CouponService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }
    
    #[Route('/home', name: 'home')]
    public function index(OpeningstijdenRepository $openingstijdenRepository,
                          CouponService $couponService, Request $request,
                          EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // All members
        $users = $userRepository->findUsersOnly();

        // Checks die worden uitgevoerd bij het bezoeken van de homepagina
        $couponService->makeCoupon();
        $couponService->checkCoupon();
        $couponService->checkDiscountSeason();

        // Openingstijden tabel
        $openingstijden = $openingstijdenRepository->findBy([],['id' => 'ASC']);

        // Handmatig Coupon code aanmaken
        $coupon = new Coupon();
        $formCoupon = $this->createForm(CouponType::class, $coupon);

        $formCoupon->handleRequest($request);
        if($formCoupon->isSubmitted() && $formCoupon->isValid()){
            $today = new \DateTimeImmutable();
            $deleteDate = clone $today;

            $coupon->setCode($formCoupon->get('code')->getData());
            $coupon->setDiscount($formCoupon->get('discount')->getData());
            $coupon->setCreatedAt(new \DateTimeImmutable());
            $coupon->setDeleteDate($deleteDate->modify("+7 day"));

            $entityManager->persist($coupon);
            $entityManager->flush();

            foreach ($users as $user){
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('bko_website@outlook.com', 'Restaurant | Berke'))
                        ->to($user->getEmail())
                        ->subject('ITS DISCO(UNT) TIME!')
                        ->html('
                    <h5>Get '. $formCoupon->get('discount')->getData() .'% off with '. $formCoupon->get('code')->getData() .'</h5>')
                );
            }

            $this->addFlash('success', 'Coupon code is succesvol aangemaakt!');
            return $this->redirectToRoute('admin_home');
        }

        // Random coupon code maken
        $formRandomCoupon = $this->createForm(RandomCouponType::class, $coupon);

        $formRandomCoupon->handleRequest($request);
        if($formRandomCoupon->isSubmitted() && $formRandomCoupon->isValid()){
            $length = 6;
            $word = array_merge(range('A', 'Z'));
            shuffle($word);
            $discount = rand(5, 75);
            $code = substr(implode($word), 0, $length) . strval($discount);

            $today = new \DateTimeImmutable();
            $deleteDate = clone $today;

            $coupon->setCode($code);
            $coupon->setDiscount($discount);
            $coupon->setCreatedAt(new \DateTimeImmutable());
            $coupon->setDeleteDate($deleteDate->modify("+7 day"));

            $entityManager->persist($coupon);
            $entityManager->flush();

            foreach ($users as $user){
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('bko_website@outlook.com', 'Restaurant | Berke'))
                        ->to($user->getEmail())
                        ->subject('ITS DISCO(UNT) TIME!')
                        ->html('
                    <h5>Get '. $discount .'% off with '. $code .'</h5>')
                );
            }

            $this->addFlash('success', 'Random coupon code is succesvol toegevoegd!');
            return $this->redirectToRoute('admin_home');
        }

        // Nieuwe discount season aanmaken
        $discountSeason = new DiscountSeason();
        $form = $this->createForm(DiscountSeasonType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $date = $form->get('date')->getData();
            $deleteDate = clone $date;

            $discountSeason->setDate($date);
            $discountSeason->setDeleteDate($deleteDate->modify("+7 day"));
            $discountSeason->setActive(false);

            $entityManager->persist($discountSeason);
            $entityManager->flush();

            $this->addFlash('success', 'Discount Season is succesvol toegevoegd!');
            return $this->redirectToRoute('admin_home');
        }

        return $this->render('admin/home.html.twig', [
            'openingstijden' => $openingstijden, 'discountSeason' => $form->createView(), 'customCoupon' => $formCoupon->createView(), 'randomCoupon' => $formRandomCoupon->createView()
        ]);
    }



    #[Route('/openingstijden/{id}', name: 'times')]
    public function times($id, OpeningstijdenRepository $openingstijdenRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $day = $openingstijdenRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(OpeningstijdenType::class, $day);
        $openingstijden = new Openingstijden();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $openingstijden->setDay($day->getDay());
            $openingstijden->setstartTime($form->get('startTime')->getData());
            $openingstijden->setendTime($form->get('endTime')->getData());
            $entityManager->flush();

            $this->addFlash('success', 'Openingstijden zijn succesvol gewijzigd!');
            return $this->redirectToRoute('admin_home');
        }

        return $this->render('admin/openingstijd.html.twig', [
            'day' => $day, 'openingstijdenForm' => $form->createView()
        ]);
    }

    #[Route('/members', name: 'members')]
    public function showMembers(UserRepository $userRepository): Response
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
        OrderRepository $orderRepository, ReservationRepository $reservationRepository,
        Filesystem $filesystem
    ): Response
    {
        $member = $userRepository->find($id);
        $reviews = $reviewRepository->findBy(['user' => $id]);
        $orders = $orderRepository->findBy(['user' => $id]);
        $reservations = $reservationRepository->findBy(['user' => $id]);

        $picture = $member->getPicture();
        if ($picture != 'defaultProfile.png'){
            $projectDir = $this->getParameter('kernel.project_dir');
            $filesystem->remove($projectDir.'/public/img/profile/'.$picture);
        }

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