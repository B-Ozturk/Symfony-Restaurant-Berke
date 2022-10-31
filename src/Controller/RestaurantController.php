<?php
// src/Controller/RestaurantController.php
namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\CategoryRepository;

class RestaurantController extends AbstractController
{
//    Home pagina
    #[Route('/')]
    public function home(): Response
    {
        $test = "als je dit ziet dan werkt het";

        return $this->render('restaurant/home.html.twig', [
            'test' => $test
        ]);
    }

//    Menu pagina
    #[Route('/menu', name: 'app_menu')]
    public function menu(CategoryRepository $CategoryRepository)
    {
        $categories = $CategoryRepository->findAll();

        return $this->render('restaurant/menu.html.twig', [
            "categories" => $categories
        ]);
    }

//    Item pagina
    #[Route('menu/{category.id}', name: 'app_item', methods:['GET', 'HEAD'])]
    public function item(MenuRepository $MenuRepository):Response
    {
        $category_id = $_GET['id'];

        $items = $MenuRepository->findBy(['category' => $category_id]);

        return $this->render('restaurant/item.html.twig', [
            "items" => $items
        ]);
    }

//    Review pagina
    #[Route('/reviews')]
    public function review(ReviewRepository $ReviewRepository): Response
    {
        $reviews = $ReviewRepository->findBy([], ['date' => 'DESC']);

        return $this->render('restaurant/reviews.html.twig', [
            "reviews" => $reviews
        ]);
    }
}