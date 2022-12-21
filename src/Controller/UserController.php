<?php
namespace App\Controller;

use App\Entity\Order;
use App\Form\EditPasswordType;
use App\Form\EditProfileType;
use App\Repository\OpeningstijdenRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ReservationRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
//  User home page
    #[Route('/home', name: 'home')]
    public function userHome(OpeningstijdenRepository $openingstijdenRepository): Response
    {
        $openingstijden = $openingstijdenRepository->findBy([],['id' => 'ASC']);

        return $this->render('user/home.html.twig', [
            'openingstijden' => $openingstijden,
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function userProfile(): Response
    {
        $user = $this->getUser();

        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/profile/edit', name: 'edit_profile')]
    public function userProfileEdit(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user->setName($form->get('name')->getData());
            $user->setEmail($form->get('email')->getData());
            $user->setTel($form->get('tel')->getData());

            $picture = $form->get('picture')->getData();

            if ($picture){
                if($user->getPicture() !== null){
                    if (file_exists($this->getParameter('kernel.project_dir') . $user->getPicture())){
                        $this->getParameter('kernel.project_dir') . $user->getPicture();
                    }
                    $newFileName = uniqid() . '.' . $picture->guessExtension();

                    try {
                        $picture->move(
                            $this->getParameter('kernel.project_dir') . '/public/img/profile', $newFileName
                        );
                    } catch (FileException $e){
                        return new Response($e->getMessage());
                    }

                    $user->setPicture($newFileName);
                    $entityManager->flush();

                    $this->addFlash('success', 'Profiel is succesvol aangepast!');
                    return $this->redirectToRoute('user_profile');
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_change_profile_complete');
        }

        return $this->render('user/edit_profile.html.twig', [
            'user' => $user, 'profile_form' => $form->createView()
        ]);
    }

    #[Route('/profile/picture/delete', name: 'delete_profile_picture')]
    public function userProfilePicture(EntityManagerInterface $entityManager, Filesystem $filesystem): Response
    {
        $user = $this->getUser();

        $picture = $user->getPicture();
        $projectDir = $this->getParameter('kernel.project_dir');
        $filesystem->remove($projectDir.'/public/img/profile/'.$picture);


        $user->setPicture('defaultProfile.png');
        $entityManager->flush();

        $this->addFlash('success', 'Profiel foto is succesvol verwijderd!');
        return $this->redirectToRoute('user_profile');
    }

    #[Route('/profile/password', name: 'edit_password')]
    public function userPasswordEdit(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $plainPassword = $form->get('password')->getData();
            $repeatPlainPassword = $form->get('repeatPassword')->getData();

            if ($repeatPlainPassword === $plainPassword){
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Wachtwoord is succesvol gewijzigd!');
                return $this->redirectToRoute('user_profile');
            } else {
                echo "<script>alert('Ingevoerde wachtwoorden komen niet overeen!')</script>";
            }


        }

        return $this->render('user/edit_password.html.twig', [
            'password_form' => $form->createView()
        ]);
    }

    #[Route('/profile/{id}/reservations', name: 'reservations')]
    public function showReservations($id, ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findBy(['user' => $id],['day' => 'DESC']);

        return $this->render('show_user/showReservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/profile/{id}/orders', name: 'orders')]
    public function showOrders($id, OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy(['user' => $id]);

        return $this->render('show_user/showOrders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/profile/order/{id}', name: 'order', methods:['GET', 'HEAD'])]
    public function showOrder(Order $order, OrderItemRepository $orderItemRepository): Response
    {
        $orderItem = $orderItemRepository->findBy(['orderRef' => $order]);

        $orderItemQuantity = array_map(function ($o){ return $o->getQuantity(); }, $orderItem);

        return $this->render('show_user/showOrder.html.twig', [
            'order' => $order, 'amount_array' => $orderItemQuantity
        ]);
    }

    #[Route('/profile/delete/{id}', name: 'delete_profile')]
    public function deleteProfile
    (   $id, UserRepository $userRepository, ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage,
        SessionInterface $session, Filesystem $filesystem, OrderRepository $orderRepository,
        ReservationRepository $reservationRepository,
    ): Response
    {
        $user = $userRepository->find($id);
        $reviews = $reviewRepository->findBy(['user' => $user]);
        $orders = $orderRepository->findBy(['user' => $user]);
        $reservations = $reservationRepository->findBy(['user' => $user]);

        $picture = $user->getPicture();
        $projectDir = $this->getParameter('kernel.project_dir');
        $filesystem->remove($projectDir.'/public/img/profile/'.$picture);

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
        if ($user){
            $entityManager->remove($user);
        }

        $entityManager->flush();
        $tokenStorage->setToken(null);
        $session->invalidate();

        $this->addFlash('success', 'Uw profiel is succesvol verwijderd!');
        return $this->redirectToRoute('app_logout');
    }
}