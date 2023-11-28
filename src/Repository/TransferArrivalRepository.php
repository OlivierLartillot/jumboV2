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
    public function findAllByCreatedAt($date): array
    {

        return $this->createQueryBuilder('t')
                    ->where('t.createdAt >= :dateStart')
                    ->andWhere('t.createdAt <= :dateEnd')
                    ->setParameter('dateStart', $date->format('Y-m-d 00:00:00'))
                    ->setParameter('dateEnd', $date->format('Y-m-d 23:59:59'))
                    ->getQuery()
                    ->getResult();
    }

    /**
     * @return 
     * retourne un tableau des arrivées multiples pour un meme compte (customer_card)
     */
    public function findMultiplesArrivals() :array
    {
        $tableauFinalDesDoublons = [];
        $results = $this->createQueryBuilder('t')
                    ->select('t as transferArrival', 'count(t.id) as count', 't.duplicateIgnored')
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
     * @return TransferArrival[] Returns an array of customersCards at this 
     * - choosen date by staff, 
     * - grouped by staff, agency and arrival hotel
     * Attribution des meetings
     */
    public function meetingRegroupmentByDayStaffAgencyAndHotel($date, $staff) :array
    {

        

        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\CustomerCard', 'c', 'WITH', 'c.id = t.customerCard')
            ->andWhere('t.meetingAt >= :dateStart')
            ->andWhere('t.meetingAt <= :dateEnd')
            ->andWhere('t.staff = :staff')
            ->setParameter('dateStart', $date->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $date->format('Y-m-d 23:59:59'))
            ->setParameter('staff', $staff)
            ->groupBy('t.staff', 'c.agency' ,'t.toArrival', 't.meetingAt', 't.meetingPoint')      
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return int the Sum of paxes by regroupment 
     * Attribution des représentants
     */
    public function paxForRegroupementHotelAndAgencies($hotel, $agency, $staff, $age, $meetingAt, $meetingPoint, $flightNumber = null)
    {

        $requete = $this->createQueryBuilder('t');

        if ($age == "adults") { $requete = $requete->select('sum(t.adultsNumber)');} 
        elseif ($age == "children") { $requete = $requete->select('sum(t.childrenNumber)');} 
        else { $requete = $requete->select('sum(t.babiesNumber)') ;}

     $requete = $requete
            ->innerJoin('App\Entity\CustomerCard', 'c', 'WITH', 'c.id = t.customerCard')
            ->andWhere('t.staff = :staff')
            ->andWhere('t.meetingAt = :meetingAt')
            ->andWhere('t.meetingPoint = :meetingPoint')
            ->andWhere('t.toArrival = :hotel')
            ->andWhere('c.agency = :agency')
            ->setParameter('meetingAt', $meetingAt) 
            ->setParameter('meetingPoint', $meetingPoint) 
            ->setParameter('hotel', $hotel)
            ->setParameter('agency', $agency)
            ->setParameter('staff', $staff);

            if ($flightNumber != null) {
                $requete = $requete
                ->andWhere('t.flightNumber = :flightNumber')
                ->setParameter('flightNumber', $flightNumber);
            }
            $requete = $requete
            ->getQuery()
            ->getSingleScalarResult()
        ;
        return $requete;
    }

    /**
     * @return TransferArrival[] Returns an array of CustomerCard objects by staff and meeting date (day)
     */
    public function findByStaffAndMeetingDate($staff, $dateTimeImmutable, $hour=null): array
    {


        $dateTime = $dateTimeImmutable->format('Y-m-d');
        $meeting = $dateTimeImmutable->format($dateTime . ' ' . $hour);
  
        $requete = $this->createQueryBuilder('t')
            ->andWhere('t.staff = :staff')
            ->andWhere('t.meetingAt >= :date_start')
            ->andWhere('t.meetingAt <= :date_end')
            ->setParameter('staff', $staff)
            ->setParameter('date_start', $dateTimeImmutable->format($dateTime . ' 00:00:00'))
            ->setParameter('date_end',   $dateTimeImmutable->format($dateTime . ' 23:59:59'));

        if ($hour != null) {
            $requete = $requete->andWhere('t.meetingAt = :hour')
                               ->setParameter('hour', $meeting);
        }

         return $requete->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return TransferArrival[] Returns an array of CustomerCard objects by staff and meeting date (day) + hotel and agency 
     * Attribution des représentants
     */
    public function findCustomersByDateHotelAgency($date, $hotel, $agency, $flightNumber = null, $meetingAt=null, $meetingPoint = null): array
    {

        $requete = $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\CustomerCard', 'c', 'WITH', 'c.id = t.customerCard')
            ->andWhere('t.meetingAt >= :dateStart')
            ->andWhere('t.meetingAt <= :dateEnd')
            ->andWhere('t.toArrival = :hotel')
            ->andWhere('c.agency = :agency')
            ->setParameter('dateStart', $date->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $date->format('Y-m-d 23:59:59'))
            ->setParameter('hotel', $hotel)
            ->setParameter('agency', $agency);
        
        
            if ($flightNumber != null) {
                $requete = $requete
                ->andWhere('t.flightNumber = :flightNumber')
                ->setParameter('flightNumber', $flightNumber)     ;
            }
            if ($meetingAt != null) {
                $requete= $requete->andWhere('t.meetingAt = :meetingAt')->setParameter('meetingAt', $meetingAt);
            }
            if ($meetingPoint != null) {
                $requete= $requete->andWhere('t.meetingPoint = :meetingPoint')->setParameter('meetingPoint', $meetingPoint);
            }        
        
            $requete = $requete
            ->getQuery()
            ->getResult()
        ;
        return $requete;
    }

    /**
     * @return TransferArrival[] Returns an array of CustomerCard objects by meeting date (day) and 
     * si l on a besoin de checker si l agence est activée (ex dans les étiquettes)
     */
    public function findByMeetingDate($dateTimeImmutable, $airports = [], $agencies = []): array
    {

        $dateTime = $dateTimeImmutable->format('Y-m-d');

        $query = $this->createQueryBuilder('t');

       
        $query = $query->leftJoin('App\Entity\CustomerCard', 'c', 'WITH', 'c.id = t.customerCard');

        // $query équivalent de in array   
        
        $query = $query->andWhere($query->expr()->in('t.fromStart', ':airport'))->setParameter('airport', $airports);
       
       
        $query = $query->andWhere($query->expr()->in('c.agency', ':agency'))->setParameter('agency', $agencies);
        

        $query = $query->andWhere('t.date >= :date_start')->andWhere('t.date <= :date_end')->andWhere('t.meetingPoint is not null');

        $query = $query->setParameter('date_start', $dateTimeImmutable->format($dateTime . ' 00:00:00'))
        ->setParameter('date_end',   $dateTimeImmutable->format($dateTime . ' 23:59:59'));

        $query= $query->orderBy('t.flightNumber', 'ASC');
        $query = $query->getQuery()->getResult();

        return $query;

    }



    /**
     * @return TransferArrival[] Returns an array of TransferArrival at this 
     * - choosen date by staff, 
     * This return the first customerCard of each groupment
     * Attribution des meetings
     */
    public function meetingRegroupmentPax($date, $staff) :array
    {

        return $this->createQueryBuilder('t')
            ->leftJoin('App\Entity\CustomerCard', 'c', 'WITH', 'c.id = t.customerCard')
            ->andWhere('c.meetingAt >= :dateStart')
            ->andWhere('c.meetingAt <= :dateEnd')
            ->andWhere('c.staff = :staff')
            ->setParameter('dateStart', $date->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $date->format('Y-m-d 23:59:59'))
            ->setParameter('staff', $staff)      
            ->getQuery()
            ->getResult()
        ;
    }


    // team Manager

    /**
     * @return TransferArrival[] Returns an array of CustomerCard objects by staff and meeting date (day) + hotel and agency 
     * Attribution des représentants
     */
    public function findByForAttribbutionRep($date, $hotel, $agency): array
    {
        
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = t.customerCard')
            ->andWhere('t.staff is null')
            ->andWhere('t.meetingAt >= :dateStart')
            ->andWhere('t.meetingAt <= :dateEnd')
            ->andWhere('t.toArrival = :hotel')
            ->andWhere('customerCard.agency = :agency')
            ->setParameter('dateStart', $date->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $date->format('Y-m-d 23:59:59'))
            ->setParameter('hotel', $hotel)
            ->setParameter('agency', $agency)
            ->getQuery()
            ->getResult()
        ;
    }

        /**
     * @return TransferArrival[] Returns a int, countnumber of items
     */
    public function countNumberNonAttributedMeetingsByDate($date): int
    {

        return $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->andWhere('t.staff IS NOT NULL')
            ->andWhere('t.meetingAt = :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }



    /**
     *
     * nombre de pax attribués pour un rep à ce jour
     */
    public function staffPaxByDate($staff,$date, $age)
    {
        //$date = $date->format('Y-m-d');
        $requete = $this->createQueryBuilder('t');

        if ($age == "adults") { $requete = $requete->select('sum(t.adultsNumber)');} 
        elseif ($age == "children") { $requete = $requete->select('sum(t.childrenNumber)');} 
        else { $requete = $requete->select('sum(t.babiesNumber)') ;}
       
        return 
            $requete = $requete
            ->andWhere('t.meetingAt >= :dateStart')
            ->andWhere('t.meetingAt <= :dateEnd')
            ->andWhere('t.staff = :staff')
            ->setParameter('dateStart', $date->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $date->format('Y-m-d 23:59:59')) 
            ->setParameter('staff', $staff)     
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return TransferArrival[] Returns an array of each date for transferArrival without rep 
     * Attribution des représentants
     */
    public function datesForCustomersWithoutRep() :array
    {

        return $this->createQueryBuilder('t')
            ->select('t.date')
            ->where('t.staff is null')
            ->groupBy('t.date')      
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TransferArrival[] Returns an array of TransferArrival objects by the day, the nature transfer and service number
     * Cette requête sert à des vérifications pendant l import du csv
     */
    public function findByDateNaturetransferClientnumber($reservationNumber, $date, $client): array
    { 

        $dateTimeImmutable = new DateTimeImmutable($date);
        $date = $dateTimeImmutable->format("Y-m-d");


        return $this->createQueryBuilder('t')
                    ->innerJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = t.customerCard')
                    ->andWhere('customerCard.reservationNumber = :reservationNumber')
                    ->andwhere('customerCard.holder = :client')
                    ->andwhere('t.date = :date')
                    ->setParameter('reservationNumber', $reservationNumber)
                    ->setParameter('client', $client)
                    ->setParameter('date', $date)
                    ->getQuery()
                    ->getResult()
        ;
    }

    /**
     * @return TransferArrival[] Returns an array of TransferArrival objects by the day, the nature transfer and service number
     * Cette requête sert à des vérifications pendant l import du csv
     */
    public function findDatesWithSkippedClients($rep): array
    { 
        return $this->createQueryBuilder('t')
            ->select('t.date')
            ->where('t.staff = :rep')
            ->setParameter('rep', $rep)
            ->groupBy('t.date')      
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TransferArrival[] counts number of time arrival exist EXCEPT TODAY
     * Cette requête sert à des vérifications pendant l import du csv
     */
    public function CheckIfArrivalExistAnotherDay($reservationNumber, $date): int
    { 

        $dateTimeImmutable = new DateTimeImmutable($date);
        $date = $dateTimeImmutable->format("Y-m-d");

     

        return $this->createQueryBuilder('t')
                ->select('count(t.id)')
                ->innerJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = t.customerCard')
                ->andWhere('t.date != :date')
                ->andWhere('customerCard.reservationNumber = :reservationNumber')
                ->setParameter('reservationNumber', $reservationNumber)
                ->setParameter('date', $date)
                ->getQuery()
                ->getSingleScalarResult()
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
