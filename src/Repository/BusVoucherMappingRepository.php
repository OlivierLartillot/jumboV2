<?php

namespace App\Repository;

use App\Entity\BusVoucherMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BusVoucherMapping>
 *
 * @method BusVoucherMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method BusVoucherMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method BusVoucherMapping[]    findAll()
 * @method BusVoucherMapping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BusVoucherMappingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusVoucherMapping::class);
    }

//    /**
//     * @return BusVoucherMapping[] Returns an array of BusVoucherMapping objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BusVoucherMapping
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
