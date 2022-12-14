<?php
// src/Controller/RestaurantController.php
namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\MenuReviewRepository;
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
            'categories' => $categories
        ]);
    }

//    Item pagina
    #[Route('menu/{id}', name: 'app_item', methods:['GET', 'HEAD'])]
    public function item($id, MenuRepository $MenuRepository, CategoryRepository $categoryRepository):Response
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        $items = $MenuRepository->findBy(['category' => $id]);

        return $this->render('restaurant/item.html.twig', [
            'items' => $items, 'category' => $category
        ]);
    }

    //    Detail pagina van een item
    #[Route('menu/item/{id}', name: 'item_detail', methods:['GET', 'HEAD'])]
    public function itemDetail($id, MenuRepository $MenuRepository, MenuReviewRepository $menuReviewRepository):Response
    {
        $item = $MenuRepository->findOneBy(['id' => $id]);
        $reviews = $menuReviewRepository->findBy(['menu' => $id], ['date' => 'DESC']);

        return $this->render('restaurant/detail.html.twig', [
            'menuItem' => $item, 'reviews' => $reviews
        ]);
    }


//    Review pagina
    #[Route('/reviews')]
    public function review(ReviewRepository $ReviewRepository): Response
    {
        $reviews = $ReviewRepository->findBy([], ['date' => 'DESC']);

        return $this->render('restaurant/reviews.html.twig', [
            'reviews' => $reviews
        ]);
    }
}