<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
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

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findFiltered(array $filters)
    {
        $qb = $this->createQueryBuilder('s');

        if (isset($filters['campus'])) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $filters['campus']);
        }

        if (isset($filters['nom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filters['nom'] . '%');
        }

        if (isset($filters['dateDebut'])) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $filters['dateDebut']);
        }

        if (isset($filters['dateFin'])) {
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $filters['dateFin']);
        }

        if (isset($filters['organisateur'])) {
            $qb->join('s.organisateur', 'o')
                ->andWhere('o.id = :organisateur')
                ->setParameter('organisateur', $filters['organisateur']);
        }

        if (isset($filters['inscrit'])) {
            $qb->leftJoin('s.users', 'p')
                ->andWhere('p.id = :inscrit')
                ->setParameter('inscrit', $filters['inscrit']);
        }

        if (isset($filters['nonInscrit'])) {
            $qb->leftJoin('s.users', 'p')
                ->andWhere('p.id != :nonInscrit OR p.id IS NULL')
                ->setParameter('nonInscrit', $filters['nonInscrit']);
        }

        if (isset($filters['passee'])) {
            $qb->join('s.etat', 'e')
                ->andWhere('e.libelle = :libelleEtat')
                ->setParameter('libelleEtat', 'Passée');
        } else {
            $qb->join('s.etat', 'e')
                ->andWhere('e.libelle != :libelleEtat')
                ->setParameter('libelleEtat', 'Passée');
        }

        return $qb->getQuery()->getResult();
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
