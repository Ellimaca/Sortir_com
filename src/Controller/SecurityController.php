<?php

namespace App\Controller;

use App\Entity\ProfilePicture;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\ByteString;

class SecurityController extends AbstractController
{
    const REGEX_PASSWORD = "^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$^";
    const REGEX_PSEUDO = "^[a-zA-Z0-9]+$^";
    //const REGEX_NAME = "^[A-Za-zàâçéèêëîïôûùüÿñæœ' -]*$^";
    const REGEX_NAME = "^[a-zA-Z]+$^";
    const REGEX_PHONE_NUMBER = "^(?:(?:(?:\+|00)33[ ]?(?:\(0\)[ ]?)?)|0){1}[1-9]{1}([ .-]?)(?:\d{2}\1?){3}\d{2}$^";

    const WARNING_PSEUDO_CHAR_NOT_AUTHORIZED = "Seulement les lettres et les chiffres sont acceptés, pas d'accents ou de caractères spéciaux autorisés";
    const WARNING_NAME_CHAR_NOT_AUTHORIZED = "Le format du prénom n'est pas valide. Caractères autorisés : a-z, ', -, ";
    const WARNING_PHONE_NUMBER_CHAR_NOT_AUTHORIZED = "Veuillez entrer un format de téléphone valide (00-00-00-00-00)";

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

        //Création d'une photo de profil
        $profilePicture = new ProfilePicture();

        //Création du formulaire que j'associe avec mon utilisateur
        $form = $this->createForm(UserType::class, $profilUser);

        $form->handleRequest($request);


        //Vérification du formulaire
        if ($form->isSubmitted()) {

            $form->isValid();

            if ($this->checkNotNullFields($profilUser)){

                //Vérification du pseudo
                $this->verifyRegex($profilUser->getPseudo(),
                    self::REGEX_PSEUDO,
                    $form, 'pseudo',
                    self::WARNING_PSEUDO_CHAR_NOT_AUTHORIZED);

                //Vérification du FirstName
                $this->verifyRegex($profilUser->getFirstName(),
                    self::REGEX_NAME,
                    $form, 'firstName',
                    self::WARNING_NAME_CHAR_NOT_AUTHORIZED);

                //Vérification du LastName
                $this->verifyRegex($profilUser->getLastName(),
                    self::REGEX_NAME,
                    $form, 'lastName',
                    self::WARNING_NAME_CHAR_NOT_AUTHORIZED);

//            //Vérification du telephone
//            $this->verifyRegex($profilUser->getPhoneNumber(),
//                self::REGEX_PHONE_NUMBER,
//                $form, 'phoneNumber',
//                self::WARNING_PHONE_NUMBER_CHAR_NOT_AUTHORIZED);

            }

            if ($form->isValid()) {

                //On récupère les données de la photo
                /** @var UploadedFile $downloadedPicture */
                $downloadedPicture = $form->get('my_file')->getData();

                //Si la photo a bien été téléchargée...
                if ($downloadedPicture) {
                    $newNamePicture = ByteString::fromRandom(30) . '.' . $downloadedPicture->guessExtension();

                    try {
                        $downloadedPicture->move(__DIR__ . '/../../public/profile/img', $newNamePicture);
                    } catch (\Exception $e) {
                        dd($e->getMessage());
                    }

                    //On ajoute la photo au profil du user
                    $profilePicture->setUser($profilUser);
                    $profilePicture->setFileName($newNamePicture);

                    $manager->persist($profilePicture);
                    $manager->flush();
                }

                //Récupération des données dans le champ "Nouveau mot de passe"
                $newPassword = $form['newPassword']->getData();

                //si l'utilisateur ne change pas son mot de passe alors il garde le même
                if ($newPassword == null) {
                    $profilUser->setPassword($this->getUser()->getPassword());
                    $manager->persist($profilUser);
                    $manager->flush();

                    $this->addFlash('success', 'Profil modifié ! ');

                } //si l'utilisateur change son mot de passe...
                else {
                    //Vérification avec l'appel de la fonction si les contraintes sont respectées.
                    if ($this->verifyConstraintsNewPassword($newPassword)) {
                        $passwordEncoded = $passwordEncoder->encodePassword($profilUser, $newPassword);
                        $profilUser->setPassword($passwordEncoded);
                        $manager->persist($profilUser);
                        $manager->flush();
                        $this->addFlash('success', 'Profil modifié ! ');
                    }
                }
            }
        }


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
        if (!preg_match(self::REGEX_PASSWORD, $newPassword)) {
            $this->addFlash('warning', 'Le mot de passe doit contenir 8 caractères minimum, au moins une lettre ET un chiffre');
        } else {
            return true;
        }

        return $isVerifiedConstraints;
    }

    /**
     * Fonction permettant de faire les vérifications type Regex sur le nouveau mot de passe
     * @param $newPassword
     * @return bool
     */
    public function verifyRegex(string $string,
                                string $regex,
                                FormInterface $form = null,
                                string $field ,
                                string $errorMessage ): bool
    {
        $isVerifiedConstraints = false;

        //Minimum 8 caractères, au moins une lettre et un chiffre:
        if (preg_match($regex, $string)) {
            $isVerifiedConstraints = true;
        } else {
            if ($form != null) {
                $form->get($field)->addError(new FormError($errorMessage));
            }
        }

        return $isVerifiedConstraints;
    }

    public function checkNotNullFields(User $profilUser):bool{
        $isOk = true;

        if($profilUser->getEmail() == null){
            $isOk = false;
        }

        if($profilUser->getFirstName() == null){
            $isOk = false;
        }

        if($profilUser->getLastName() == null){
            $isOk = false;
        }

        if($profilUser->getPhoneNumber() == null){
            $isOk = false;
        }

        if($profilUser->getPseudo() == null){
            $isOk = false;
        }

        return $isOk;

    }

    /**
     * Fonction affichage du profil d'un participant à un évènement
     * @Route("/profil/consulter/{id}", name="profil_view")
     */
    public function displayProfile($id, UserRepository $userRepository)
    {
        $userChoosen = $userRepository->find($id);

        if (!$userChoosen) {
            throw $this->createNotFoundException("Participant inconnu");
        }

        return $this->render('participant/view.html.twig', ['userChoosen' => $userChoosen
        ]);
    }

}




