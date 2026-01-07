<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\Models\SortieSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
     * Finds sorties based on filters and optimizes query using eager loading.
     */
    public function findByFilters(SortieSearch $filters, ?Participant $user): array
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect('c', 'o', 'e')
            ->leftJoin('s.campus', 'c')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('s.etat', 'e');

        $this->applyFilters($qb, $filters, $user);

        // Default sorting by start date (most recent first or upcoming first?)
        // Usually DESC for "latest created" or ASC for "upcoming". 
        // Let's stick to default or let controller decide? 
        // Original code didn't have explicit sort, likely ID or natural order.
        // Adding a logical sort by date is cleaner.
        $qb->addOrderBy('s.dateHeureDebut', 'DESC');

        return $qb->getQuery()->getResult();
    }

    private function applyFilters(QueryBuilder $qb, SortieSearch $filters, ?Participant $user): void
    {
        // 1. Campus
        if ($filters->getCampus()) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $filters->getCampus());
        }

        // 2. Name Search
        if ($filters->getSortieNom()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%'.$filters->getSortieNom().'%');
        }

        // 3. Start Date
        if ($filters->getPremiereDate()) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $filters->getPremiereDate()->format('Y-m-d'));
        }

        // 4. End Date
        if ($filters->getDerniereDate()){
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $filters->getDerniereDate()->format('Y-m-d 23:59:59'));
        }

        // 5. Organizer (Me)
        if ($filters->isSortiesOrganisees()){
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        // 6. Registered (Me)
        if ($filters->isSortiesInscrites()) {
            $qb->join('s.participants', 'p_inscrit')
                ->andWhere('p_inscrit = :user')
                ->setParameter('user', $user);
        }

        // 7. Not Registered (Me)
        if ($filters->isSortiesNonInscrites()) {
            $qb->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        // 8. Passed
        if ($filters->isSortiesPassees()) {
            // Using alias 'e' defined in findByFilters
            $qb->andWhere('e.libelle = :libellePassee')
                ->setParameter('libellePassee', 'Activité terminée');
        }

        // Global filter: Exclude Archived
        // Using alias 'e'
        $qb->andWhere('e.libelle != :libelleArchivee')
            ->setParameter('libelleArchivee', 'Activité archivée');
    }
}
