<?php

namespace App\Controller;

use App\Command\CreateUsersFromCsvFileCommand;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
/**
 * @Route("/admin/", name="admin_")
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("enregistrementUtilisateur", name="enregistrement")
     */
    public function register(Request $request, UserPasswordEncoderInterface $userPasswordEncoderInterface,
                                CreateUsersFromCsvFileCommand $createUsersFromCsvFileCommand
                                ): Response
    {
        $user = new User();
        $user->setRoles(["ROLE_USER"]);
        $user->setAdmin(0);
        $user->setActif(1);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordEncoderInterface->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('sortie_accueil');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/fichier", name="app_fichier")
     */
    public function enregistreFichierCsv (Request $request,
                                          UserPasswordEncoderInterface $userPasswordEncoderInterface,
                                          CreateUsersFromCsvFileCommand $createUsersFromCsvFileCommand
    ): Response{
        $directory = '\wamp64\www\ENIsortie.com\public\data';


        if ($request->getMethod()==Request::METHOD_POST){
            $file = $request->getContent();
                dd($file);
            $file->move($directory, $file->getClientOriginalName());
            $createUsersFromCsvFileCommand->createUsers($file->getClientOriginalName());

            $this->addFlash('message', "Utilistaurs créés en BDD");

            return $this->redirectToRoute('sortie_accueil');
        }

        return $this->render('registration/fichier.html.twig');
    }


}
