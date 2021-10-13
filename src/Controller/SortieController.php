<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\CreationSortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/", name="sortie_")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("/", name="")
     */
    public function accueil(SortieRepository $sortieRepository): Response
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/home", name="liste")
     */
    public function liste(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->affichageSortieAccueil();
        return $this->render('main/home.html.twig', [
            'sorties' => $sorties,
        ]);
    }


    /**
     * @Route("/CreateSortie/", name="creationSortie")
     */
    public function create(
        Request                $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        SortieRepository $sortieRepository

    ): Response
    {
        $currentUser = $this->getUser();


            $sortie = new Sortie();

            $sortie->setOrganisateur($currentUser);
            $sortie->setCampus($currentUser->getCampus());
            $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

            $sortieForm->handleRequest($request);

            if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                $sortie->setEtat($etat);
                $entityManager->persist($sortie);
                $entityManager->flush();

                $this->addFlash('success', 'Sortie ajoutée avec succès');
                return $this->redirectToRoute('sortie_liste');
            }
            return $this->render('sortie/creationSortie.html.twig', [
                'sortieForm' => $sortieForm->createView(),
            ]);

    }
//   /**
//     *
//     * @Route("/Sortie/{id}", name="SortiePublish")
//     */
//
//    public function publish(
//        Request                $request,
//        EntityManagerInterface $entityManager,
//        EtatRepository $etatRepository,
//        SortieRepository $sortieRepository,
//        int $id=0) {
//
//
//        $sortie = $sortieRepository->find($id);
//        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);
//
//        $sortieForm->handleRequest($request);
//
//        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
//            $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
//            $sortie->setEtat($etat);
//            $entityManager->persist($sortie);
//            $entityManager->flush();
//
//            $this->addFlash('success', 'Sortie ajoutée avec succès');
//            return $this->redirectToRoute('sortie_liste');
//
//        }
//        return $this->render('sortie/creationSortie.html.twig', [
//            'sortieForm' => $sortieForm->createView(),
//        ]);
//
//    }


}

