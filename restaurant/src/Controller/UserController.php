<?php
namespace App\Controller;

use App\Entity\Order;
use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user')]
class UserController extends AbstractController
{
//  User home page
    #[Route('/home', name: '_home')]
    public function userhome(): Response
    {
        $test = "als je dit ziet dan werkt het";

        return $this->render('user/home.html.twig', [
            'test' => $test
        ]);
    }

    #[Route('/ordercomplete', name:'_order_complete')]
    public function userOrderComplete(): Response
    {
        $orderComplete = "Uw bestelling wordt klaar gemaakt en naar u verzonden!";

        return $this->render('user/action_complete.html.twig', [
        'text' => $orderComplete
        ]);
    }

    //  User review complete
    #[Route('/reviewcomplete', name: '_review_complete')]
    public function userReviewComplete(): Response
    {
        $reviewComplete = "Uw review is successvol geplaatst!";

        return $this->render('user/action_complete.html.twig', [
            'text' => $reviewComplete
        ]);
    }

    //  User reserveren complete
    #[Route('/reserverencomplete', name: '_reserveren_complete')]
    public function userReserverenComplete(): Response
    {
        $reserverenComplete = "Uw reservering is successvol geplaatst!";

        return $this->render('user/action_complete.html.twig', [
            'text' => $reserverenComplete
        ]);
    }

    //  User bestellen complete
    #[Route('/bestellencomplete', name: '_bestellen_complete')]
    public function userBestellenComplete(): Response
    {
        $bestellingComplete = "Uw bestelling is successvol geplaatst!";

        return $this->render('user/action_complete.html.twig', [
            'text' => $bestellingComplete
        ]);
    }
}