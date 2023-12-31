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

    /**
     * @return TransferInterHotel Returns an array of CustomerCard objects by the day, the nature transfer and service number
     * Cette requête sert à des vérifications pendant l import du csv
     */
/*     public function findByDateAndCustomerCard($customerCard, $date): array
    { 

        return $this->createQueryBuilder('t')
                    ->innerJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = t.customerCard')
                    ->andWhere('customerCard.id = :customerCard')
                    ->andwhere('t.date = :date')
                    ->setParameter('date', $date)
                    ->setParameter('customerCard', $customerCard)
                    ->getQuery()
                    ->getResult()
        ;
    } */
     /**
     * @return TransferInterHotel Returns an array of TransferInterHotel objects by the day, and the staff
     * 
     */ 
    public function finfByStaffAndDate($date, $staff): array
    { 
        return $this->createQueryBuilder('d')
                    /* ->innerJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = d.customerCard') */
                    ->select('d')
                    ->innerJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'transferArrival.customerCard = d.customerCard')
                    ->andwhere('d.date = :date')
                    ->andwhere('transferArrival.staff = :staff')
                    ->setParameter('date', $date)
                    ->setParameter('staff', $staff)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @return TransferInterHotel[] Returns an array of TransferInterHotels 
     * 
     */
    public function findInterHotelsBydatesAndCompanies($dateStart, $dateEnd, $company, $area, $type): array
    {

        $dateStart = new DateTimeImmutable($dateStart);
        $dateEnd = new DateTimeImmutable($dateEnd);

        $requete = $this->createQueryBuilder('t')
            ->andWhere('t.date >= :dateStart and t.date <= :dateEnd')
            ->setParameter('dateStart', $dateStart->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $dateEnd->format('Y-m-d 23:59:59'));

        if ($company != 'all') {
            $requete = $requete
            ->andWhere('t.transportCompany = :company') 
            ->setParameter('company', $company);
        }
        if ($area != 'all') {
            $requete = $requete
            ->andWhere('t.area = :area') 
            ->setParameter('area', $area);
        } 
        if ($type != 'all') {
            $requete = $requete
            ->andWhere('t.isCollective = :isCollective') 
            ->setParameter('isCollective', $type);
        }
           

        $requete = $requete
            ->getQuery()
            ->getResult()
        ;

        return $requete;

    }

    /**
     * zones unique pour le transferVehicule
     */
    public function findTransferInterhotelAreas(): array
    {
        return $this->createQueryBuilder('t')
                    ->select('t.area')
                    ->distinct()
                    ->getQuery()
                    ->getResult()
                ;
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
