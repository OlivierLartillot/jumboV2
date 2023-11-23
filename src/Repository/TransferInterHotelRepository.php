<?php

namespace App\Repository;

use App\Entity\TransferInterHotel;
use DateTimeImmutable;
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

    /**
     * @return CustomerCard[] Returns an array of CustomerCard objects by the day, the nature transfer and service number
     * Cette requête sert à des vérifications pendant l import du csv
     */
    public function findByDateNaturetransferClientnumber($reservationNumber, $date): array
    { 

        $dateTimeImmutable = new DateTimeImmutable($date);
        $date = $dateTimeImmutable->format("Y-m-d");

        return $this->createQueryBuilder('t')
                    ->innerJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = t.customerCard')->andWhere('t.date = :date')
                    ->andWhere('customerCard.reservationNumber = :reservationNumber')
                    ->andwhere('t.date = :date')
                    ->setParameter('reservationNumber', $reservationNumber)
                    ->setParameter('date', $date)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @return 
     * retourne un tableau des interHotel multiples pour un meme compte (customer_card)
     */
    public function findMultiplesInterHotels() :array
    {
        $tableauFinalDesDoublons = [];
        $results = $this->createQueryBuilder('t')
                    ->select('t as transferInterHotel', 'count(t.id) as count', 't.duplicateIgnored')
                    ->where('t.duplicateIgnored = false')
                    ->groupBy('t.customerCard')
                    ->getQuery()
                    ->getResult();

        foreach ($results as $result) {
            if ( ($result['count'] > 1 )  and ($result['duplicateIgnored'] === false) ) {

                $tableauFinalDesDoublons[] = $result;
            }
        }

        return $tableauFinalDesDoublons;
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
