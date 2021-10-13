<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
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
        $er = $this->getEntityManager()->getRepository(Etat::class);
        $etat = $er->findOneBy(['libelle' => 'Clôturée']);
        $query = $queryBuilder -> getQuery();

        $sorties = $query->getResult();
        foreach ($sorties as $sortie)
        {
            if($sortie->getDateLimite() < new \DateTime() || $sortie->getNombreInscriptionsMax() === sizeof($sortie->getParticipant()))
            {
                $sortie->setEtat($etat);
                $this->_em->persist($sortie);
            }
        }
        $this->_em->flush();
        return $sorties;
    }

    public function filtreSortieAccueil($nom, $campus, $date1, $date2)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->leftJoin('s.etat', 'e')->addSelect('e');
        $queryBuilder->leftJoin('s.organisateur', 'u')->addSelect('u');
        $queryBuilder->leftJoin('s.participant', 'su')->addSelect('su');
        $queryBuilder->setParameter('nom', '%'.$nom.'%');
        $queryBuilder->where('s.nom LIKE :nom');
        $queryBuilder->setParameter('firstDate', $date1);
        $queryBuilder->setParameter('lastDate', $date2);
        $queryBuilder->where('s.date BETWEEN :firstDate AND :lastDate');
        $er = $this->getEntityManager()->getRepository(Etat::class);
        $etat = $er->findOneBy(['libelle' => 'Clôturée']);
        $query = $queryBuilder -> getQuery();

        $sorties = $query->getResult();
        foreach ($sorties as $sortie)
        {
            if($sortie->getDateLimite() < new \DateTime() || $sortie->getNombreInscriptionsMax() === sizeof($sortie->getParticipant()))
            {
                $sortie->setEtat($etat);
                $this->_em->persist($sortie);
            }
        }
        $this->_em->flush();
        return $sorties;
    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}
