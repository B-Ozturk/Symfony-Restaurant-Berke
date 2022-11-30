<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class ShowUserController extends AbstractController
{
    #[Route('/show/user', name: '_show_user')]
    public function index(): Response
    {
        return $this->render('show_user/home.html.twig', [
            'controller_name' => 'ShowUserController',
        ]);
    }
}
