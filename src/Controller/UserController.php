<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profil", name="profile_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="profile")
     */
    public function profile(Request $request)
    {
        $user = $this->getUser();

        $getForm = $this->createForm(ProfileType::class, $user);

        $getForm->handleRequest($request);

        if ($getForm->isSubmitted() && $getForm->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('message', 'Votre profil a bien été modifier !!');
            return $this->redirectToRoute('sortie_liste', ['id' => $user->getId()]);

        }

        return $this->render('user/profile.html.twig', [
            'getForm' => $getForm->createView()
        ]);
    }

    /**
     * @Route("/modifier", name="editProfile")
     */
    public function editProfile(UserRepository $userRepository, Request $request)
    {
        $user = $this->getUser();

        if($userRepository->find($user)){

            $editProfileForm = $this->createForm(ProfileType::class, $user);

            $editProfileForm->handleRequest($request);
            if ($editProfileForm->isSubmitted() && $editProfileForm->isValid()){
                $entityManager = $this->getDoctrine()->getManager();
                dump($user);
                $entityManager->persist($user);
                dump($user);
                $entityManager->flush();

                $this->addFlash('message', 'Votre profil a bien été modifier !!');
                return $this->redirectToRoute('sortie_liste', ['id' => $user->getId()]);

            }
        }


        return $this->render('user/editProfile.html.twig', [
            'editProfileForm' => $editProfileForm->createView()
        ]);
    }
}