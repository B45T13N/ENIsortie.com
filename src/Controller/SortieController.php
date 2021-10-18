<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\CancelType;
use App\Form\CreationSortieType;
use App\Form\FilterType;
use App\Form\LieuType;
use App\Form\VilleType;
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
            $lieu = new Lieu();
            $lieuForm = $this->createForm(LieuType::class, $lieu);
            $lieuForm->handleRequest($request);
            if($lieuForm->isSubmitted() && $lieuForm->isValid()){

            $entityManager->persist($lieu);
            $entityManager->flush();
            $this->addFlash('Success', 'Votre lieu a été ajoutée avec succès');

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
                'lieuForm'=> $lieuForm->createView(),
                'sortieForm' => $sortieForm->createView(),
            ]);

    }

    /**
     * @Route("/ModifySortie/{idSortie}", name="modifSortie")
     */
    public function modify(
        Request                $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        SortieRepository $sortieRepository,
        int $idSortie
    ): Response
    {
        $sortie = $sortieRepository->find($idSortie);
        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);

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
     * @Route("/affichageSortie/{id}", name="affichageSortie")
     */
    public function affichageSortie(SortieRepository $sortieRepository, Request $request, int $id): Response
    {

        $sortieDetails = $sortieRepository->affichageSortieDetails($id);
        $listeParticipants = $sortieDetails[0]->getParticipant();
        return $this->render('sortie/affichageSortie.html.twig',
                ["sortieDetails" => $sortieDetails[0],
                "listeParticipants" => $listeParticipants
            ]);



    }


    /**
     * @Route("/publierSortie/{idSortie}", name="publierSortie")
     */
    public function publierSortie(
        Request                $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        SortieRepository $sortieRepository,
        int $idSortie
    ): Response
    {
        $sortie = $sortieRepository->find($idSortie);
        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie publiée avec succès');
            return $this->redirectToRoute('sortie_liste');
        }
        return $this->render('sortie/publierSortie.html.twig', [
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

    /**
     * @Route("/RegisterSortie/{idSortie}", name="registrationSortie")
     */
    public function register(int $idSortie, SortieRepository $sortieRepository, EntityManagerInterface $entityManager){


        $user = $this->getUser();
        $sortie = $sortieRepository->find($idSortie);
        if(new \DateTime() > $sortie->getDate() && new \DateTime() > $sortie->getDateLimite() && sizeof($sortie->getParticipant()) < $sortie->getNombreInscriptionsMax()) {
            $this->addFlash("Error", "T'es trop lent, la sortie n'est plus dispo !");
        } else{
            $sortie->addParticipant($user);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_liste');

    }

    /**
     * @Route("/seDesister/{idSortie}", name="seDesisterSortie")
     */
    public function seDesister(int $idSortie, SortieRepository $sortieRepository, EntityManagerInterface $entityManager){


        $user = $this->getUser();
        $sortie = $sortieRepository->find($idSortie);

        if($sortie->getDate() < new \DateTime()) {
            $this->addFlash("Error", "Tu ne peux plus de désinscrire petit coquin !");
        }else{
            $sortie->removeParticipant($user);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
        return $this->redirectToRoute('sortie_liste');

    }

    /**
     * @Route("/ajouterVille/", name="ajouterVille")
     */
    public function creerVille(Request $request, EntityManagerInterface $entityManager){

        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);
        if($villeForm->isSubmitted() && $villeForm->isValid()){

            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash('Success', 'Votre ville a été ajoutée avec succès');
            return $this->redirectToRoute('sortie_liste');
        }

        return $this->render('sortie/creationVille.html.twig', [
            'villeForm' => $villeForm->createView()
        ]);
    }



    /**
     * @Route("/ajouterLieu/", name="ajouterLieu")
     */
    public function ajouterLieu(
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);
        if($lieuForm->isSubmitted() && $lieuForm->isValid()){

            $entityManager->persist($lieu);
            $entityManager->flush();
            $this->addFlash('Success', 'Votre lieu a été ajoutée avec succès');
            return $this->redirectToRoute('sortie_creationSortie');
        }

        return $this->render('sortie/createLieu.html.twig', [
            'lieuForm' => $lieuForm->createView(),
        ]);

    }

}

