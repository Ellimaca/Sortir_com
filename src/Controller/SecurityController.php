<?php

namespace App\Controller;

use App\Entity\ProfilePicture;
use App\Entity\User;
use App\Form\ProfilePictureType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\AppAuthentificatorAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use Symfony\Component\String\ByteString;

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
                           Request $request,
                           UserPasswordEncoderInterface $passwordEncoder)
    {

        /** @var User $user */
        //Je récupère les infos de l'utilisateur connecté
        $profilUser = $userRepository->find($this->getUser());

        //je crée le form et j'associe mon formulaire et mon profil ensemble
        $form = $this->createForm(UserType::class, $profilUser);

        $form->handleRequest($request);

        //je vérifie que si le formulaire est soumit il est bien valide
        if ($form->isSubmitted() && $form->isValid()) {
                if($profilUser->getPassword() == null) {
                    $profilUser->setPassword($this->getUser()->getPassword());
                }
            //$passwordConfirmation = $form['passwordConfirmation']->getData();
            //$password = $form['password']->getData();

           // if ($passwordConfirmation == $password) {
                $manager->persist($profilUser);
                $manager->flush();

                $this->addFlash('success', 'Profil modifié ! ');

                //Si les mots de passe correspondent, je redirige vers son profil

                //return $this->redirectToRoute('security_profil');
                //Sinon j'ajoute un message un message d'erreur
           // } else {
                $this->addFlash("warning", "Le mot de passe ne correspond pas à la confirmation");
                //et je redirige vers mon profil avec le message d'erreur
               // return $this->redirectToRoute('security_profil');
           // }

        }

        //Je crée ensuite mon formulaire photo
        $profilePicture = new ProfilePicture();

        $pictureForm = $this->createForm(ProfilePictureType::class, $profilePicture);

        $pictureForm->handleRequest($request);

        if ($pictureForm->isSubmitted() && $pictureForm->isValid()) {
            /** UploadedFile $telechargementPhoto */
            $downloadPicture = $pictureForm->get('file')->getData();
            $newPictureName = ByteString::fromRandom(30) . '.' . $downloadPicture->guessExtension();

            try {
                $downloadPicture->move(__DIR__ . '/../../public/profile/img', $newPictureName);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }

            //Je récupère le user connecté
            $user = $this->getUser();

            //J'ajoute à mon user la photo de profil
            $profilePicture->setUser($user);
            $profilePicture->setFileName($newPictureName);

            $manager->persist($profilePicture);
            $manager->flush();

            $this->addFlash('success', 'Photo de profil bien ajouté !');
        }

        //et je renvoie vers son profil
        return $this->render('security/profil.html.twig', [
            'modifForm' => $form->createView(),
            'pictureForm' => $pictureForm->createView()
        ]);
    }


}




