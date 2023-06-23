<?php

namespace App\Repository;

use App\Entity\TransferInterHotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferInterHotel>
 *
 * @method TransferInterHotel|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferInterHotel|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferInterHotel[]    findAll()
 * @method TransferInterHotel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferInterHotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferInterHotel::class);
    }

    public function save(TransferInterHotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferInterHotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TransferInterHotel[] Returns an array of TransferInterHotel objects
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

//    public function findOneBySomeField($value): ?TransferInterHotel
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
