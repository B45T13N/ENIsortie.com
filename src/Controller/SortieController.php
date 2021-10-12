<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/", name="sortie_")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("/", name="liste")
     */
    public function accueil(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->affichageSortieAccueil();
        return $this->render('main/home.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
