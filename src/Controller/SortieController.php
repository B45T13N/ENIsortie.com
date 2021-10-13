<?php

namespace App\Controller;

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
        $sorties = $sortieRepository->affichageSortieAccueil();
        return $this->render('base.html.twig', [
            'sorties' => $sorties,
        ]);
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
        EtatRepository $etatRepository
    ): Response
    {
            $currentUser = $this->getUser();
            $sortie = new Sortie();
            $sortie->setOrganisateur($currentUser);
            $sortie->setCampus($currentUser->getCampus());
            $sortieForm = $this->createForm(CreationSortieType::class, $sortie);
            if($sortie->getDateLimite()>$sortie->getDate()){
                $this->addFlash(
                 'Vous devez avoir une date de clôture inférieur à la date de l"évenement ! '
                );
            }

            $sortieForm->handleRequest($request);
            $etat = $etatRepository;
            if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
                if($request->request->get('cree')){
                    $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                } else {
                    $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                }
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


}

