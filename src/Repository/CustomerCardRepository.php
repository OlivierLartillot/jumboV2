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


        

        $requete = $this->createQueryBuilder('c');
        

         // todo ! Pour les transferts arrivée/interHotel/departure, géréer dans l affichage ! nous avons toujours besoin de select arrival car on utilise les données dans l affichage !!!!
            $requete = $requete->leftJoin('App\Entity\TransferJoan', 'transferJoan', 'WITH', 'c.id = transferJoan.customerCard')
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->leftJoin('App\Entity\TransferInterHotel', 'transferInterHotel', 'WITH', 'c.id = transferInterHotel.customerCard')
            ->leftJoin('App\Entity\TransferDeparture', 'transferDeparture', 'WITH', 'c.id = transferDeparture.customerCard')
            ->leftJoin('App\Entity\AirportHotel', 'airportHotel', 'WITH', 'airportHotel.id = transferArrival.fromStart OR airportHotel.id = transferArrival.toArrival')
            ;
        
                
        //si la date 1 est avant l'intervalle et que la date deux est différent d'avant l intervalle
        
        // si la date1 est choisie 
        
        // si la date
            if ($customerPresence == "on") {
                $requete = $requete->andWhere('(transferArrival.date = :dateStart and transferArrival.date = :dateEnd) 
                                            or (transferInterHotel.date = :dateStart and transferInterHotel.date = :dateEnd)
                                            or (transferDeparture.date = :dateStart and transferDeparture.date = :dateEnd)' )
                ->setParameter('dateStart', $dateStart->format('Y-m-d'))
                ->setParameter('dateEnd', $dateEnd->format('Y-m-d'))
                ;

            }  else {
                if ($dateStart != "") {
                    $requete = $requete->andWhere('transferArrival.date >= :dateStart
                                                or transferInterHotel.date >= :dateStart
                                                or transferDeparture.date >= :dateStart')->setParameter('dateStart', $dateStart);
                }
                if ($dateEnd != "") { 
                    $requete = $requete->andWhere('transferArrival.date <= :dateEnd
                                                or transferInterHotel.date <= :dateEnd
                                                or transferDeparture.date <= :dateEnd')->setParameter('dateEnd',  $dateEnd);
                } 
            }

                            
        // si la date 1 ou la date 2 est dans l'intervalle
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








        if ($flightNumber != "all") {
            $requete = $requete->andWhere('transferArrival.flightNumber LIKE :transferArrival')->setParameter('transferArrival', '%'.$flightNumber.'%');
        }

        $requete = $requete ->getQuery()->getResult();


       //dd($requete);

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
