<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CancelType;
use App\Form\CreationSortieType;
use App\Form\FilterType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

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
    public function liste(SortieRepository $sortieRepository, Request $request): Response
    {

        $sortie = new Sortie();
        $sortie->setDate(new \DateTime());
        $sortie->setDateLimite(new \DateTime('+1 year'));
        $filtreForm = $this->createForm(FilterType::class, $sortie);
        $sorties = $sortieRepository->affichageSortieAccueil();


        $filtreForm->handleRequest($request);

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $data = $filtreForm->getData();
            if($data->getNom())
            {
                $sorties = $sortieRepository->filtreSortieAccueil($data->getNom(), $data->getCampus(), $data->getDate(), $data->getDateLimite());
            } else
            {
                $sorties = $sortieRepository->filtreSortieAccueil($data->getNom(), $data->getCampus(), $data->getDate(), $data->getDateLimite());
            }
            $this->addFlash('success', 'Votre recherche :');
            return $this->render('main/home.html.twig', [
                'sorties' => $sorties,
                'filtreForm' => $filtreForm->createView(),
            ]);
        }


        return $this->render('main/home.html.twig', [
            'sorties' => $sorties,
            'filtreForm' => $filtreForm->createView(),
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
                $this->addFlash('error',
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

    /**
     *
     * @Route("/cancelSortie/{idSortie}", name="cancelSortie")
     */
    public function cancel(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository, SortieRepository $sortieRepository, int $idSortie)
    {

        $sortie = $sortieRepository->find($idSortie);
        $etat = $etatRepository->findOneBy(['libelle'=>'Annulée']);

        $cancelForm = $this->createForm(CancelType::class, $sortie);

        $cancelForm->handleRequest($request);


        if($cancelForm->isSubmitted() && $cancelForm->isValid()){

            $sortie -> setEtat($etat);

            $newDescription = $cancelForm['description'] -> getData();
            $sortie->setDescription((string)$newDescription);

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('Success', 'Votre sortie a été annulée avec succès');
            return $this->redirectToRoute('sortie_liste');
        }
        return $this->render('sortie/cancelSortie.html.twig', [
            'cancelForm' => $cancelForm->createView(),
            'sortie' => $sortie,
        ]);
    }


}

