<?php

namespace App\Repository;

use App\Entity\DiscountSeason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscountSeason>
 *
 * @method DiscountSeason|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscountSeason|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscountSeason[]    findAll()
 * @method DiscountSeason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscountSeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscountSeason::class);
    }

    public function save(DiscountSeason $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscountSeason $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDiscountSeasonByDate($formattedCurrentDate)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.delete_date LIKE :date')
            ->setParameter('date', "%$formattedCurrentDate%")
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return DiscountSeason[] Returns an array of DiscountSeason objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DiscountSeason
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
