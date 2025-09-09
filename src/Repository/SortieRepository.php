<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\Models\SortieSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

// Fais par Samir
class SortieRepository extends ServiceEntityRepository
{


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFilters(SortieSearch $filters, ?Participant $user): array
    {
        $qb = $this->createQueryBuilder('s');

        $this->applyFilters($qb, $filters, $user);

        return $qb->getQuery()->getResult();
    }

    private function applyFilters(QueryBuilder $qb, SortieSearch $filters, ?Participant $user): void
    {
        $now = new \DateTime();

        if ($filters->getCampus()) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $filters->getCampus());
        }
        if ($filters->getSortieNom()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%'.$filters->getSortieNom().'%');
        }
        if ($filters->getPremiereDate()) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $filters->getPremiereDate()->format('Y-m-d'));
        }
        if ($filters->getDerniereDate()){
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $filters->getDerniereDate()->format('Y-m-d'));
        }

        if ($filters->isSortiesOrganisees()){
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user );
        }

        if ($filters->isSortiesInscrites()) {
            $qb->join('s.participants', 'p_inscrit')
                ->andWhere('p_inscrit = :user')
                ->setParameter('user', $user);
        }

        if ($filters->isSortiesNonInscrites()) {
            $qb->leftJoin('s.participants', 'p_non_inscrit')
                ->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if ($filters->isSortiesPassees()) {
            $qb->join('s.etat', 'e');
            $qb->andWhere('e.libelle = :libelle')
                ->setParameter('libelle', 'Activité terminée');
        }

            $qb->join('s.etat', 'stat');
            $qb->andWhere('stat.libelle != :libelle')
                ->setParameter('libelle', 'Activité archivée');



    }
















    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
