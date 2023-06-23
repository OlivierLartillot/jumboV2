<?php

namespace App\Repository;

use App\Entity\CustomerCard;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerCard>
 *
 * @method CustomerCard|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerCard|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerCard[]    findAll()
 * @method CustomerCard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerCard::class);
    }

    public function save(CustomerCard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CustomerCard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return CustomerCard[] Returns an array of CustomerCard objects by now (day)
     */
    public function findByNow(): array
    {

        $dateTimeImmutable = new DateTimeImmutable("now", new DateTimeZone('America/Santo_Domingo'));
        $dateTime = $dateTimeImmutable->format("Y-m-d");
        //America/Dominica / America/Santo_Domingo
/*         $timezone_identifiers = DateTimeZone::listIdentifiers( DateTimeZone::AMERICA );
        dd(join( ', ', $timezone_identifiers ));
 */

     return $this->createQueryBuilder('c')
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->leftJoin('App\Entity\TransferInterHotel', 'transferInterHotel', 'WITH', 'c.id = transferInterHotel.customerCard')
            ->leftJoin('App\Entity\TransferDeparture', 'transferDeparture', 'WITH', 'c.id = transferDeparture.customerCard')
            ->andWhere('transferArrival.dateHour >= :date_start')
            ->andWhere('transferArrival.dateHour <= :date_end')
            ->setParameter('date_start', $dateTimeImmutable->format($dateTime . ' 00:00:00'))
            ->setParameter('date_end',   $dateTimeImmutable->format($dateTime . ' 23:59:59'))
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }





    /**
     * @return CustomerCard[] Returns a int, countnumber of items
     */
    public function countNumberNonAttributedMeetingsByDate($date): int
    {

        /* $dateTime = $dateTimeImmutable->format('Y-m-d'); */

        return $this->createQueryBuilder('c')
             ->select('count(c.id)')
            ->andWhere('c.staff IS NOT NULL')
            ->andWhere('c.meetingAt >= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }



    /**
     * @return CustomerCard[] Returns an array of CustomerCard objects by staff and meeting date (day)
     */
    public function findByStaffAndMeetingDate($staff, $dateTimeImmutable): array
    {


        $dateTime = $dateTimeImmutable->format('Y-m-d');

  
        return $this->createQueryBuilder('c')
            ->andWhere('c.staff = :staff')
            ->andWhere('c.meetingAt >= :date_start')
            ->andWhere('c.meetingAt <= :date_end')
            ->setParameter('staff', $staff)
            ->setParameter('date_start', $dateTimeImmutable->format($dateTime . ' 00:00:00'))
            ->setParameter('date_end',   $dateTimeImmutable->format($dateTime . ' 23:59:59'))
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CustomerCard[] Returns an array of CustomerCard objects by meeting date (day) and 
     */
    public function findByMeetingDate($dateTimeImmutable): array
    {


        $dateTime = $dateTimeImmutable->format('Y-m-d');

  
        return $this->createQueryBuilder('c')
            ->andWhere('c.meetingAt >= :date_start')
            ->andWhere('c.meetingAt <= :date_end')
            ->andWhere('c.meetingPoint is not null')
            ->setParameter('date_start', $dateTimeImmutable->format($dateTime . ' 00:00:00'))
            ->setParameter('date_end',   $dateTimeImmutable->format($dateTime . ' 23:59:59'))
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CustomerCard[] Returns an array by the search User input text 
     */
    public function search($input): array
    {

        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('App\Entity\TransferJoan', 'transferJoan', 'WITH', 'c.id = transferJoan.customerCard')
            ->andWhere('c.reservationNumber LIKE :reservationNumber 
                        OR c.holder LIKE :holder
                        OR c.jumboNumber LIKE :jumboNumber
                        OR transferJoan.voucherNumber LIKE :voucherNumber
                        ')
            ->setParameter('reservationNumber', '%'.$input.'%')
            ->setParameter('holder', '%'.$input.'%')
            ->setParameter('jumboNumber', '%'.$input.'%')
            ->setParameter('voucherNumber', '%'.$input.'%')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


 /**
     * @return CustomerCard[] Returns an array of CustomerCard objects by agencies 
     */
    public function agenciesList(): array
    {

        return $this->createQueryBuilder('c')
            ->select('c.agency')
            ->distinct()
            ->orderBy('c.agency', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    public function customerCardPageSearch( DateTimeImmutable $dateStart = null, DateTimeImmutable $dateEnd = null, $customerPresence, $rep, $status, $agency, $hotel, $search, $natureTransfer, $flightNumber): ?array
    {      


         $requete = $this->createQueryBuilder('c')
                            ->leftJoin('App\Entity\TransferJoan', 'transferJoan', 'WITH', 'c.id = transferJoan.customerCard')
                            //->leftJoin('App\Entity\Transfer', 'transfer', 'WITH', 'c.id = transfer.customerCard')
                            ->leftJoin('App\Entity\AirportHotel', 'airportHotel', 'WITH', 'airportHotel.id = transfer.fromStart OR airportHotel.id = transfer.toArrival')
                            ;

        // sinon on filtre juste si la date match 
            if ($customerPresence == "on") {
                $requete = $requete->andWhere('transfer.natureTransfer = 1 and transfer.dateHour < :dateStart and transfer.natureTransfer = 3 and transfer.dateHour > :dateStart' )
                ->setParameter('dateStart', $dateStart)
                ;
            } 

            else {
                if ($dateStart != "") {
                    $requete = $requete->andWhere('transfer.dateHour > :dateStart')->setParameter('dateStart', $dateStart);
                }
                if ($dateEnd != "") { 
                    $requete = $requete->andWhere('transfer.dateHour < :dateEnd')->setParameter('dateEnd', $dateEnd);
                }

            }
           


        
        if ($rep != "all") { $requete = $requete->andWhere('c.staff = :rep')->setParameter('rep', $rep );}
        if ($status != "all") { $requete = $requete->andWhere('c.status = :status')->setParameter('status', $status );}


        // recup de l agence
        if ($agency != "all") {
            $requete = $requete->andWhere('c.agency = :agency')->setParameter('agency', $agency);
        }
        // recup de l agence
        if ($hotel != "all") {
            $requete = $requete->andWhere('airportHotel.id = :hotel')->setParameter('hotel', $hotel)->orWhere();
        }


        // SEARCH : jointure avec la table transfer Joan pour le numéro de bon
        $requete = $requete->andWhere('c.reservationNumber LIKE :reservationNumber 
                            OR c.holder LIKE :holder
                            OR c.jumboNumber LIKE :jumboNumber
                            OR transferJoan.voucherNumber LIKE :voucherNumber
                            ')
                            ->setParameter('reservationNumber', '%'.$search.'%')
                            ->setParameter('holder', '%'.$search.'%')
                            ->setParameter('jumboNumber', '%'.$search.'%')
                            ->setParameter('voucherNumber', '%'.$search.'%');

        if ($natureTransfer != "all") {
            $requete = $requete->andWhere('transfer.natureTransfer = :natureTransfer')->setParameter('natureTransfer', $natureTransfer);
        }
        if ($flightNumber != "all") {
            $requete = $requete->andWhere('transfer.flightNumber LIKE :flightNumber')->setParameter('flightNumber', '%'.$flightNumber.'%');
        }

        $requete = $requete ->getQuery()->getResult();

        return $requete;

    } 





//    public function findOneBySomeField($value): ?CustomerCard
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
