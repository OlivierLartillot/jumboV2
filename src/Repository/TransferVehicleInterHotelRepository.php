<?php

namespace App\Repository;

use App\Entity\TransferVehicleInterHotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferVehicleInterHotel>
 *
 * @method TransferVehicleInterHotel|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferVehicleInterHotel|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferVehicleInterHotel[]    findAll()
 * @method TransferVehicleInterHotel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferVehicleInterHotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferVehicleInterHotel::class);
    }

    public function save(TransferVehicleInterHotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferVehicleInterHotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TransferVehicleInterHotel[] Returns an array of TransferVehicleInterHotel objects
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

//    public function findOneBySomeField($value): ?TransferVehicleInterHotel
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
