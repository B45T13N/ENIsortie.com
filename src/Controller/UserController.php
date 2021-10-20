<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/membre/profil/", name="utilisateur_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("modifier", name="modifierProfil")
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

                $this->addFlash('success', 'Votre profil a bien été modifier !!');
                return $this->redirectToRoute('sortie_accueil', ['id' => $user->getId()]);

            }
        }


        return $this->render('user/editProfile.html.twig', [
            'editProfileForm' => $editProfileForm->createView()
        ]);
    }

    /**
     * @Route("{id}", name="profil")
     */
    public function profile(int $id, Request $request, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        return $this->render('user/profile.html.twig', ["user" => $user]);
    }
    /**
     * @Route("admin/desactiver/{id}", name="desactiver")
     */
    public function desactivateUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager, SortieRepository $sortieRepository){
        $user = $userRepository->find($id);
        $user->setActif(false);
        $sorties = $sortieRepository->findBy(['organisateur'=>$user->getId()]);
        foreach ($sorties as $sortie){
            $entityManager->remove($sortie);
        }
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur bien désactivé ');

        return $this->redirectToRoute('sortie_accueil');
    }
    /**
     * @Route("admin/supprimer/{id}", name="supprimer")
     */
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $user = $userRepository->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur bien supprimé');

        return $this->redirectToRoute('sortie_accueil');
    }

}