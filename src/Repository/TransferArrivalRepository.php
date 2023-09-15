<?php

namespace App\Repository;

use App\Entity\TransferArrival;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferArrival>
 *
 * @method TransferArrival|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferArrival|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferArrival[]    findAll()
 * @method TransferArrival[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferArrivalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferArrival::class);
    }

    public function save(TransferArrival $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferArrival $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return TransferArrival[] Returns an array of TransferArrival objects
     */
    public function findByDateAirportFlightNumberVoucherNumber($date, $airport, $flightNumber, $voucherNumber): array
    {
            
        $requete = $this->createQueryBuilder('ta')
                        ->leftJoin('App\Entity\TransferVehicleArrival', 'transferVehicleArrival', 'WITH', 'ta.customerCard = transferVehicleArrival.customerCard');

        $requete = $requete->andWhere('ta.date = :date')->setParameter('date', $date); 
        if ($airport != 'all') {
            $requete = $requete->andWhere('ta.fromStart = :airport')->setParameter('airport', $airport);
        }
        if ($flightNumber != '') {
        $requete = $requete->andWhere('ta.flightNumber LIKE :flightNumber')->setParameter('flightNumber', '%'.$flightNumber.'%');
        }
        if ($voucherNumber != '') {
            $requete = $requete->andWhere('transferVehicleArrival.voucherNumber LIKE :voucherNumber')->setParameter('voucherNumber', '%'.$voucherNumber.'%');
        }
        
        $requete = $requete->orderBy('ta.id', 'ASC')
        ->getQuery()
        ->getResult()
        ;

        return $requete;
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



//    /**
//     * @return TransferArrival[] Returns an array of TransferArrival objects
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

//    public function findOneBySomeField($value): ?TransferArrival
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
