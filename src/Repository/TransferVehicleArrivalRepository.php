<?php

namespace App\Repository;

use App\Entity\TransferVehicleArrival;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferVehicleArrival>
 *
 * @method TransferVehicleArrival|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferVehicleArrival|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferVehicleArrival[]    findAll()
 * @method TransferVehicleArrival[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferVehicleArrivalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferVehicleArrival::class);
    }

    public function save(TransferVehicleArrival $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferVehicleArrival $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TransferVehicleArrival[] Returns an array of TransferVehicleArrival objects
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

//    public function findOneBySomeField($value): ?TransferVehicleArrival
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
