<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Entity\User;
use App\Form\CancelType;
use App\Form\CreationSortieType;
use App\Form\FiltersType;
use App\Form\FilterType;
use App\Form\LieuType;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;

/**
 * @Route("/membre/", name="sortie_")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("", name="")
     */
    public function accueil(SortieRepository $sortieRepository): Response
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("accueil", name="accueil")
     */
    public function liste(SortieRepository $sortieRepository, CampusRepository $campusRepository, Request $request): Response
    {
        $nom = "";
        $dateDebut = new \DateTime();
        $dateFin = new \DateTime('+1 year');
        $campus = $campusRepository->findAll();
        $filtreForm = $this->createForm(FilterType::class, [$nom, $dateDebut, $dateFin, $campus]);
        $sorties = $sortieRepository->affichageSortieAccueil();

        $filtreForm->handleRequest($request);

        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $data = $filtreForm->getData();
            $sorties = $sortieRepository->filtreSortieAccueil($data['nom'], $data['campus'], $data['date'], $data['dateLimite']);
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
     * @Route("creerSortie", name="creerSortie")
     */
    public function create(
        Request                $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository
    ): Response
    {
        $user = $this->getUser();
        $currentUser = $this->getUser();
        $sortie = new Sortie();
        $sortie->setOrganisateur($currentUser);
        $sortie->setCampus($currentUser->getCampus());

        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        $etat = $etatRepository;
        if ($user->getActif() == false) {
            $this->addFlash("danger", "Ton compte est désactivé");
            return $this->redirectToRoute('sortie_accueil');
        } else if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
                if ($request->request->get('cree')) {
                    $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                } else {
                    $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                }
                $sortie->setEtat($etat);
                $entityManager->persist($sortie);
                $entityManager->flush();

                $this->addFlash('success', 'Sortie ajoutée avec succès');
                return $this->redirectToRoute('sortie_accueil');
            }
            return $this->render('sortie/creationSortie.html.twig', [
                'sortieForm' => $sortieForm->createView(),
            ]);
    }



    /**
     * @Route("modifierSortie/{idSortie}", name="modifierSortie")
     */
    public function modify(
        Request                $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        SortieRepository $sortieRepository,
        int $idSortie
    ): Response
    {
        $user = $this->getUser();
        $sortie = $sortieRepository->find($idSortie);
        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($user->getActif() == false) {
            $this->addFlash("danger", "Ton compte est désactivé");
            return $this->redirectToRoute('sortie_accueil');
        }else if($user->getId() != $sortie->getOrganisateur()->getId())
        {
            $this->addFlash("danger","Tu n'es pas l'organisateur de cette sortie");
            return $this->redirectToRoute('sortie_accueil');
        }else if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            if($request->request->get('cree')){
                $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
            } else {
                $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
            }
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie ajoutée avec succès');
            return $this->redirectToRoute('sortie_accueil');
        }
        return $this->render('sortie/creationSortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);

    }

    /**
     * @Route("affichageSortie/{id}", name="affichageSortie")
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
     * @Route("publierSortie/{idSortie}", name="publierSortie")
     */
    public function publierSortie(
        Request                $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        SortieRepository $sortieRepository,
        int $idSortie
    ): Response
    {
        $user = $this->getUser();
        $sortie = $sortieRepository->find($idSortie);
        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($user->getActif() == false)
        {
            $this->addFlash("danger","Ton compte est désactivé");
            return $this->redirectToRoute('sortie_accueil');
        } else if($user->getId() != $sortie->getOrganisateur()->getId())
        {
            $this->addFlash("danger","Tu n'est pas l'organisateur de cette sortie");
            return $this->redirectToRoute('sortie_accueil');
        }else if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie publiée avec succès');
            return $this->redirectToRoute('sortie_accueil');
        }
        return $this->render('sortie/publierSortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);

    }


    /**
     *
     * @Route("annulerSortie/{idSortie}", name="annulerSortie")
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
            $this->addFlash('success', 'Votre sortie a été annulée avec succès');
            return $this->redirectToRoute('sortie_accueil');
        }
        if($this->getUser()->getId() == $sortie->getOrganisateur()->getId() || $this->getUser()->getAdmin() == true) {

            return $this->render('sortie/cancelSortie.html.twig', [
                'cancelForm' => $cancelForm->createView(),
                'sortie' => $sortie,
            ]);
        } elseif($this->getUser()->getActif() == false) {

            $this->addFlash('danger', 'Ton compte est désactivé !');
            return $this->redirectToRoute('sortie_accueil');
        }else {
            $this->addFlash('danger', "Tu n'es pas l'admin ou l'organisateur de cette sortie !");
            return $this->redirectToRoute('sortie_accueil');
        }
    }

    /**
     * @Route("sinscrire/{idSortie}", name="sinscrire")
     */
    public function register(int $idSortie, SortieRepository $sortieRepository, EntityManagerInterface $entityManager)
    {


        $user = $this->getUser();
        $sortie = $sortieRepository->find($idSortie);
        if ( ($sortie->getEtat()->getLibelle() == 'Clôturée')|| (new \DateTime() > $sortie->getDate() && new \DateTime() > $sortie->getDateLimite()) || sizeof($sortie->getParticipant()) == $sortie->getNombreInscriptionsMax()) {
            $this->addFlash("danger", "T'es trop lent, la sortie n'est plus dispo !");
        } elseif($user->getActif() == false){
            $this->addFlash("danger","Ton compte est désactivé");
        } else{
            $sortie->addParticipant($user);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_accueil');

    }

    /**
     * @Route("seDesister/{idSortie}", name="seDesister")
     */
    public function seDesister(int $idSortie, SortieRepository $sortieRepository, EntityManagerInterface $entityManager){


        $user = $this->getUser();
        $sortie = $sortieRepository->find($idSortie);

        if($sortie->getDate() < new \DateTime()) {
            $this->addFlash("danger", "Tu ne peux plus de désinscrire petit coquin !");
        } else{
            $sortie->removeParticipant($user);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
        return $this->redirectToRoute('sortie_accueil');

    }

    /**
     * @Route("ajouterVille", name="ajouterVille")
     */
    public function creerVille(Request $request, EntityManagerInterface $entityManager){

        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);
        if($villeForm->isSubmitted() && $villeForm->isValid()){
            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash('success', 'Votre ville a été ajoutée avec succès');
            return $this->redirectToRoute('sortie_accueil');
        }

        if($this->getUser()->getActif() == false){
            $this->addFlash('danger', 'Ton compte est désactivé');
            return $this->redirectToRoute('sortie_accueil');
        } else {
            return $this->render('sortie/creationVille.html.twig', [
                'villeForm' => $villeForm->createView()
            ]);
        }
    }



    /**
     * @Route("ajouterLieu/", name="ajouterLieu")
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
            $this->addFlash('success', 'Votre lieu a été ajoutée avec succès');
            return $this->redirectToRoute('sortie_creerSortie');
        }

        return $this->render('sortie/createLieu.html.twig', [
            'lieuForm' => $lieuForm->createView(),
        ]);
    }

    /**
     * @Route("admin/archiver", name="archivageSortie")
     */
    public function archivage(
        Request $request,
        SortieRepository $sortieRepository
    )
    {
        $sorties = $sortieRepository->affichageSortieAccueil();
        $sortieRepository->archivage($sorties);

        $this->addFlash('success', 'Archivage réussi');

        return $this->redirectToRoute('sortie_accueil');
    }

}

