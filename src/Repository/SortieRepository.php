<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class SortieRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);

    }

    public function affichageSortieAccueil()
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.etat', 'e')->addSelect('e');
        $queryBuilder->leftJoin('s.organisateur', 'u')->addSelect('u');
        $queryBuilder->leftJoin('s.participant', 'su')->addSelect('su');
        $queryBuilder->where('e.libelle != \'Archivée\'');
        $query = $queryBuilder->getQuery();

        $sorties = $query->getResult();

        return $query->getResult();
    }

    public function filtreSortieAccueil($nom = null, $campus, $date1, $date2)
    {

        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.etat', 'e')->addSelect('e');
        $queryBuilder->leftJoin('s.organisateur', 'u')->addSelect('u');
        $queryBuilder->leftJoin('s.participant', 'su')->addSelect('su');
        if($campus != null) {
            $queryBuilder->where('s.campus = ' . $campus->getId());
        }
        if ($nom !== "") {
            $queryBuilder->andWhere('s.nom LIKE :nom');
            $queryBuilder->setParameter('nom', '%' . $nom . '%');
        }
        if($date1 != null && $date2 !=null) {
            $queryBuilder->andWhere('s.date BETWEEN :firstDate AND :lastDate');
            $queryBuilder->setParameter('firstDate', $date1)
                ->setParameter('lastDate', $date2);
        }


        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function archivage($sorties)
    {

        $er = $this->getEntityManager()->getRepository(Etat::class);
        $etatCloture = $er->findOneBy(['libelle' => 'Clôturée']);
        $etatPasse = $er->findOneBy(['libelle' => 'Passée']);
        $etatOuvert = $er->findOneBy(['libelle' => 'Ouverte']);
        $etatEnCours = $er->findOneBy(['libelle' => 'Activité en cours']);
        $etatArchivee = $er->findOneBy(['libelle' => 'Archivée']);
        $aujourdhui = new \DateTime();

        foreach ($sorties as $sortie) {

//            $datetest = $sortie->getDate();
//            $duree = new \DateInterval('PT' . $sortie->getDuree() . 'M');
//            $datefinsortie = date_add($datetest, $duree);
//            dd($sortie->getDate() === $datefinsortie);

            if($sortie->getEtat()->getLibelle() != 'Annulée') {


                if ($sortie->getEtat()->getLibelle() == 'Ouverte' && ($sortie->getDateLimite() < $aujourdhui || $sortie->getNombreInscriptionsMax() === sizeof($sortie->getParticipant()))) {
                    $sortie->setEtat($etatCloture);
                    $this->_em->persist($sortie);
                }

                if ($sortie->getEtat()->getLibelle() != 'Ouverte' && $sortie->getDate() > $aujourdhui && $sortie->getNombreInscriptionsMax() < sizeof($sortie->getParticipant())) {
                    $sortie->setEtat($etatOuvert);
                    $this->_em->persist($sortie);
                }

                if (($sortie->getEtat()->getLibelle() == 'Ouverte' || $sortie->getEtat()->getLibelle() == 'Clôturée') &&
                    $aujourdhui->format('Y-m-d') == $sortie->getDate()->format('Y-m-d')) {
                    $sortie->setEtat($etatEnCours);
                    $this->_em->persist($sortie);
                }

                if (($sortie->getEtat()->getLibelle() == 'Activité en cours' || $sortie->getEtat()->getLibelle() == 'Clôturée'
                    || $sortie->getEtat()->getLibelle() == 'Ouverte') && $aujourdhui->format('Y-m-d') > $sortie->getDate()->format('Y-m-d')) {
                    $sortie->setEtat($etatPasse);
                    $this->_em->persist($sortie);
                }

            }
            if ($sortie->getDate() < new \DateTime('-1 month')) {
                $sortie->setEtat($etatArchivee);
                $this->_em->persist($sortie);
            }
        }
        $this->_em->flush();
        return $sorties;
    }

    public function affichageSortieDetails(int $id)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.etat', 'e')->addSelect('e');
        $queryBuilder->leftJoin('s.organisateur', 'u')->addSelect('u');
        $queryBuilder->where('s.id = ' . $id);
        $query = $queryBuilder->getQuery();

        $sortie = $query->getResult();
        return $sortie;
    }

}


