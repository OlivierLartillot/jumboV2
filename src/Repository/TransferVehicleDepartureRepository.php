<?php

namespace App\Repository;

use App\Entity\TransferVehicleDeparture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferVehicleDeparture>
 *
 * @method TransferVehicleDeparture|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferVehicleDeparture|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferVehicleDeparture[]    findAll()
 * @method TransferVehicleDeparture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferVehicleDepartureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferVehicleDeparture::class);
    }

    public function save(TransferVehicleDeparture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferVehicleDeparture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TransferVehicleDeparture[] Returns an array of TransferVehicleDeparture objects
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

//    public function findOneBySomeField($value): ?TransferVehicleDeparture
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
