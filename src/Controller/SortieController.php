<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CreationSortieType;
use App\Repository\SortieRepository;
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
     * @Route("/creationSortie", name="creationSortie")
     */
    public function create(Request $request): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(CreationSortieType::class, $sortie);
        return $this->render('sortie/creationSortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);
    }
}
