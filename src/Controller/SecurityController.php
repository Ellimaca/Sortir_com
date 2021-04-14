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
            //Je récupère ce qui est rentré dans "Nouveau mot de passe"
            $newPassword = $form['newPassword']->getData();

            //si l'utilisateur ne change pas son mot de passe, alors je garde le même
            if($newPassword == null){
                $profilUser->setPassword($this->getUser()->getPassword());
                $manager->persist($profilUser);
                $manager->flush();

                $this->addFlash('success', 'Profil modifié ! ');

                //si l'utilisateur change son mot de passe...
            } else {
                //On vérifie avec l'appel de la fonction si les contraintes sont respectées.
                if($this->verifyConstraintsNewPassword($newPassword)){
                    $passwordEncoded = $passwordEncoder->encodePassword($profilUser, $newPassword);
                    $profilUser->setPassword($passwordEncoded);
                    $manager->persist($profilUser);
                    $manager->flush();
                    $this->addFlash('success', 'Profil modifié ! ');
                }

            }

        }

        //et je renvoie vers son profil
        return $this->render('security/profil.html.twig', [
            'modifForm' => $form->createView(),
        ]);
    }

    /**
     * Fonction permettant de faire les vérifications type Regex sur le nouveau mot de passe
     * @param $newPassword
     * @return bool
     */
    public function verifyConstraintsNewPassword($newPassword) {
        $isVerifiedConstraints = false;

        //Minimum 8 caractères, au moins une lettre et un chiffre:
        if(!preg_match("^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$^", $newPassword)) {
            $this->addFlash('warning', 'Le mot de passe doit contenir 8 caractères minimum, au moins une lettre ET un chiffre');
            $isVerifiedConstraints = false;
        } else {
            return true;
        }

        return $isVerifiedConstraints;
    }

    /**
     * Fonction affichage du profil d'un participant à un évènement
     * @Route("/evenement/profil/{id}", name="security-participant")
     */
    public function displayProfile()
    {

        return $this->render('security/profilParticpant.html.twig', [

        ]);
    }

}




