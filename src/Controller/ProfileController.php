<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profil", name="profile_monProfile")
     *
     */
    public function monProfile(Request $request)
        {
            $user = $this->getUser();

        $profileForm = $this->createForm(ProfileType::class, $user);

        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('message', 'Votre profil a bien été modifier !!');
            return $this->redirectToRoute('sortie_liste', ['id' => $profile->getId()]);

        }

        return $this->render('profile.html.twig', [
            'profileForm' => $profileForm->createView()
        ]);
    }
}