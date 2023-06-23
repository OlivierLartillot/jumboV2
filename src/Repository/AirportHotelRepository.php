<?php

namespace App\Repository;

use App\Entity\AirportHotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AirportHotel>
 *
 * @method AirportHotel|null find($id, $lockMode = null, $lockVersion = null)
 * @method AirportHotel|null findOneBy(array $criteria, array $orderBy = null)
 * @method AirportHotel[]    findAll()
 * @method AirportHotel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AirportHotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AirportHotel::class);
    }

    public function save(AirportHotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AirportHotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return AirportHotel[] Returns an array of AirportHotel objects Asc
     */
    public function findAllAsc(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isAirport = :isAirport')
            ->setParameter('isAirport', 0)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return AirportHotel[] Returns an array of AirportHotel objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AirportHotel
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
