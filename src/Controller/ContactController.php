<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Repository\OpeningstijdenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    #[Route('/contact')]
    public function contact(OpeningstijdenRepository $OpeningstijdenRepository, Request $request, MailerInterface $mailer): Response
    {
//        Code voor het ophalen van de openingstijden tabel uit de database
        $openingstijdenrepo = $OpeningstijdenRepository;
        $openingstijden = $openingstijdenrepo->findAll();

//       Code voor het handelen van de contact form
        $form = $this->createForm(ContactType::class);

           $form->handleRequest($request);

           if ($form->isSubmitted() && $form->isValid()) {

               $contactFormData = $form->getData();

               $begin = "Name: " . $contactFormData['name'];
               $middle = "Email: " . $contactFormData['email'];
               $date = date("d-m-Y");

                $message = (new Email())
                    ->from('bko_website@outlook.com',)
                    ->to('pofowebsite_contakt@outlook.com')
                    ->subject('Contact Form - Symfony Restaurant Berke')
                    ->text($begin ."\r\n". $middle ."\r\n". "Message: " . $contactFormData['message'] ."\r\n". "Date: " . $date, 'text/plain');

                $mailer->send($message);

                return $this->render('contact/email_send.html.twig');
           }


        return $this->render('restaurant/contact.html.twig', [
            "openingstijden" => $openingstijden , 'our_form' => $form->createView(),
        ]);
    }
}
