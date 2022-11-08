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
        return $this->render('user/home.html.twig', [
            'test' => 'als je dit ziet dan werkt het'
        ]);
    }

    #[Route('/ordercomplete', name:'_order_complete')]
    public function userOrderComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
        'text' => 'Uw bestelling wordt klaar gemaakt en naar u verzonden!'
        ]);
    }

    //  User review complete
    #[Route('/reviewcomplete', name: '_review_complete')]
    public function userReviewComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
            'text' => 'Uw review is successvol geplaatst!'
        ]);
    }

    //  User reserveren complete
    #[Route('/reserverencomplete', name: '_reserveren_complete')]
    public function userReserverenComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
            'text' => 'Uw reservering is successvol geplaatst!'
        ]);
    }

    //  User bestellen complete
    #[Route('/bestellencomplete', name: '_bestellen_complete')]
    public function userBestellenComplete(): Response
    {
        return $this->render('user/action_complete.html.twig', [
            'text' => 'Uw bestelling is successvol geplaatst!'
        ]);
    }
}