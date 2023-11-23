<?php

namespace App\Repository;

use App\Entity\TransportCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransportCompany>
 *
 * @method TransportCompany|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransportCompany|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransportCompany[]    findAll()
 * @method TransportCompany[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransportCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransportCompany::class);
    }

//    /**
//     * @return TransportCompany[] Returns an array of TransportCompany objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TransportCompany
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
