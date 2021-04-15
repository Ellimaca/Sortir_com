<?php

namespace App\Controller;

use App\Entity\ProfilePicture;
use App\Entity\User;
use App\Form\ProfilePictureType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\ByteString;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
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
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    /**
     * Fonction permettant d'afficher le profil de l'utilisateur connecté
     * @Route("/profil", name="security_profil")
     * @param EntityManagerInterface $manager
     * @param UserRepository $userRepository
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function profil(EntityManagerInterface $manager,
                           UserRepository $userRepository,
                           Request $request,
                           UserPasswordEncoderInterface $passwordEncoder): Response
    {

        /** @var User $user */
        //Récupération de mon utilisateur
        $profilUser = $userRepository->find($this->getUser());

        //Création du formulaire que j'associe avec mon utilisateur
        $form = $this->createForm(UserType::class, $profilUser);

        $form->handleRequest($request);

        /** @var UploadedFile $downloadedPicture */
        $downloadedPicture = $form->get('file')->getData();
        $newNamePicture = ByteString::fromRandom(30) . '.' . $downloadedPicture->guessExtension();

        try {
            $downloadedPicture->move(__DIR__ . '/../../public/profile/img', $newNamePicture);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }


        //Vérification du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            //Récupération des données dans le champ "Nouveau mot de passe"
            $newPassword = $form['newPassword']->getData();

            //si l'utilisateur ne change pas son mot de passe alors il garde le même
            if($newPassword == null){
                $profilUser->setPassword($this->getUser()->getPassword());
                $manager->persist($profilUser);
                $manager->flush();

                $this->addFlash('success', 'Profil modifié ! ');

                //si l'utilisateur change son mot de passe...
            } else {
                //Vérification avec l'appel de la fonction si les contraintes sont respectées.
                if($this->verifyConstraintsNewPassword($newPassword)){
                    $passwordEncoded = $passwordEncoder->encodePassword($profilUser, $newPassword);
                    $profilUser->setPassword($passwordEncoded);
                    $manager->persist($profilUser);
                    $manager->flush();
                    $this->addFlash('success', 'Profil modifié ! ');
                }

            }

        }

        //Création du formulaire photo

        //Création d'une photo de profil
       /*

            $profilePicture->setUser($profilUser);
            $profilePicture->setFileName($newNamePicture);

            $manager->persist($profilePicture);
            $manager->flush();

            $this->addFlash('success', 'Merci pour la/les photo(s)! ');

        */

        return $this->render('security/profil.html.twig', [
            'modifForm' => $form->createView(),
        ]);
    }

    /**
     * Fonction permettant de faire les vérifications type Regex sur le nouveau mot de passe
     * @param $newPassword
     * @return bool
     */
    public function verifyConstraintsNewPassword($newPassword): bool
    {
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
     * @Route("/profil/consulter/{id}", name="profil_view")
     */
    public function displayProfile($id, UserRepository $userRepository)
    {
        $userChoosen = $userRepository->find($id);

        if (!$userChoosen){
            throw $this->createNotFoundException("Participant inconnu");
        }

        return $this->render('participant/view.html.twig', [ 'userChoosen' => $userChoosen
        ]);
    }

}




