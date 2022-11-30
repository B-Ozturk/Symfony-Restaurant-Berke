<?php
namespace App\Controller;

use App\Entity\Order;
use App\Form\EditPasswordType;
use App\Form\EditProfileType;
use App\Form\RegistrationFormType;
use App\Repository\MenuRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormEvent;
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
    public function userHome(): Response
    {
        return $this->render('user/home.html.twig', [
            'test' => 'als je dit ziet dan werkt het'
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

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_change_profile_complete');
        }

        return $this->render('user/edit_profile.html.twig', [
            'user' => $user, 'profile_form' => $form->createView()
        ]);
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

                return $this->redirectToRoute('user_change_profile_complete');
            } else {
                echo "<script>alert('Ingevoerde wachtwoorden komen niet overeen!')</script>";
            }


        }

        return $this->render('user/edit_password.html.twig', [
            'password_form' => $form->createView()
        ]);
    }

    #[Route('/profile/delete/{id}', name: 'delete_profile')]
    public function deleteProfile($id, UserRepository $userRepository, ReviewRepository $reviewRepository , EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, SessionInterface $session): Response
    {
        $user = $userRepository->find($id);
        $review = $reviewRepository->findOneBy(['user' => $user]);

        if ($review){
            $entityManager->remove($review);
        } else {
//            nothing
        }

        if ($user){
            $entityManager->remove($user);
        } else {
//            if the user doesn't exist something went wrong while writing this code
        }

        $entityManager->flush();
        $tokenStorage->setToken(null);
        $session->invalidate();

        return $this->redirectToRoute('app_logout');

    }


    #[Route('/profile/complete', name: 'change_profile_complete')]
    public function userProfileChangeComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
            'text' => 'Profiel is succesvol aangepast!'
        ]);
    }

    #[Route('/ordercomplete', name:'order_complete')]
    public function userOrderComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
        'text' => 'Uw bestelling wordt klaar gemaakt en naar u verzonden!'
        ]);
    }

    //  User review complete
    #[Route('/reviewcomplete', name: 'review_complete')]
    public function userReviewComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
            'text' => 'Uw review is successvol geplaatst!'
        ]);
    }

    //  User reserveren complete
    #[Route('/reserverencomplete', name: 'reserveren_complete')]
    public function userReserverenComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
            'text' => 'Uw reservering is successvol geplaatst!'
        ]);
    }

    //  User bestellen complete
    #[Route('/bestellencomplete', name: 'bestellen_complete')]
    public function userBestellenComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
            'text' => 'Uw bestelling is successvol geplaatst!'
        ]);
    }
}