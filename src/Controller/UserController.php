<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/profil", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/modifier", name="editProfile")
     */
    public function editProfile(UserRepository $userRepository, Request $request, UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $user = $this->getUser();
        $directory = $this->getParameter('directory');

        if($userRepository->find($user)){

            $editProfileForm = $this->createForm(ProfileType::class, $user);
            $editProfileForm->handleRequest($request);

            if ($editProfileForm->isSubmitted() && $editProfileForm->isValid()){
                $user->setPassword(
                    $userPasswordEncoderInterface->encodePassword(
                        $user,
                        $editProfileForm->get('plainPassword')->getData()
                    )
                );
                $file = $editProfileForm['photo']->getData();
                $file->move($directory, $file->getClientOriginalName());
                $user->setPhoto($file->getClientOriginalName());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('message', 'Votre profil a bien été modifier !!');
                return $this->redirectToRoute('sortie_liste', ['id' => $user->getId()]);

            }
        }


        return $this->render('user/editProfile.html.twig', [
            'editProfileForm' => $editProfileForm->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="profile")
     */
    public function profile(int $id, Request $request, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        return $this->render('user/profile.html.twig', ["user" => $user]);
    }
}