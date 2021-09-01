<?php

namespace App\Controller;

use App\Entity\Newsletters\Users;
use App\Form\NewslettersUsersType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * @Route("/users", name="users")
     */
    public function index(Request $request, \Swift_Mailer $mailer): Response
    {
        $user = new Users();
        $form = $this->createForm(NewslettersUsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('send@example.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'email/inscription.html.twig',
                        ['categories' => $user->getCategories()]
                    ),
                    'text/html'
                );
            $mailer->send($message);
            return $this->render('email/inscription.html.twig', [
                'categories' => $user->getCategories(),
            ]);
        }

        return $this->render('users/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
