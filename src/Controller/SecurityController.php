<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    /**
     * @Route("/profil", name="security_profil")
     */
    public function profil(EntityManagerInterface $manager,
                           UserRepository $userRepository,
                           Request $request)
    {

        //Je récupère les infos de l'utilisateur connecté
        $profilUser = $this->getUser();

        //je crée le form et j'associe mon formulaire et mon profil ensemble
        $form = $this->createForm(UserType::class, $profilUser);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $passwordConfirmation = $form['passwordConfirmation']->getData();
            $password = $form['password']->getData();

            if($passwordConfirmation == $password) {
                $manager->persist($profilUser);
                $manager->flush();

                $this->addFlash('success', 'Profil modifié ! ');

                return $this->redirectToRoute('main');

            } else {
                $this->addFlash("warning", "Le mot de passe ne correspond pas à la confirmation");
                return $this->redirectToRoute('security_profil');
            }

        }


        return $this->render('security/profil.html.twig', [
           'modifForm' => $form->createView()
        ]);


    }

}
