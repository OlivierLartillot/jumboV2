<?php

namespace App\Repository;

use App\Entity\CustomerCard;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Integer;

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
            ->andWhere('transferArrival.date >= :date_start')
            ->andWhere('transferArrival.date <= :date_end')
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
        
         $requete = $requete->leftJoin('App\Entity\TransferJoan', 'transferJoan', 'WITH', 'c.id = transferJoan.customerCard');

        // Les résultats dépendent de la nature du transfert
        if ($natureTransfer == "all") {
            $requete = $requete
                            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
                            ->leftJoin('App\Entity\TransferInterHotel', 'transferInterHotel', 'WITH', 'c.id = transferInterHotel.customerCard')
                            ->leftJoin('App\Entity\TransferDeparture', 'transferDeparture', 'WITH', 'c.id = transferDeparture.customerCard')
                            ->leftJoin('App\Entity\AirportHotel', 'airportHotel', 'WITH', 'airportHotel.id = transferArrival.fromStart OR airportHotel.id = transferArrival.toArrival')
            ;
            if ($customerPresence == 2) {
                $requete = $requete->orWhere('transferArrival.date >= :dateStart AND transferArrival.date <= :dateEnd')
                                    ->orWhere('transferInterHotel.date >= :dateStart AND transferInterHotel.date <= :dateEnd')
                                    ->orWhere('transferDeparture.date >= :dateStart AND transferDeparture.date <= :dateEnd')

                ->setParameter('dateStart', $dateStart->format('Y-m-d'))
                ->setParameter('dateEnd', $dateEnd->format('Y-m-d'))
                ;

            }  else {
                $requete = $requete->andWhere('transferArrival.date >= :dateStart and transferArrival.date <= :dateEnd')
                                    ->orWhere('transferDeparture.date >= :dateStart and transferDeparture.date <= :dateEnd')
                                    ->orWhere('(transferArrival.date <= :dateStart 
                                                and transferArrival.date <= :dateEnd) 
                                                and (transferDeparture.date >= :dateStart 
                                                or transferDeparture.date is null)')
                                    ->orWhere('(transferArrival.date >= :dateStart 
                                                and transferArrival.date >= :dateEnd)
                                                and (transferDeparture.date >= :dateStart and transferDeparture.date <= :dateEnd 
                                                or transferDeparture.date is null)')
  
/*                                     ->orWhere('transferArrival.date <= :dateStart and transferDeparture.date >= :dateStart')
                                    ->orWhere('transferArrival.date >= :dateStart and transferDeparture.date is null') */
                                    ->setParameter('dateStart',  $dateStart->format('Y-m-d'))->setParameter('dateEnd', $dateEnd->format('Y-m-d'));

            }
        }
        elseif ($natureTransfer == 1){
            $requete = $requete->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->leftJoin('App\Entity\AirportHotel', 'airportHotel', 'WITH', 'airportHotel.id = transferArrival.fromStart OR airportHotel.id = transferArrival.toArrival');
            if ($customerPresence == 2) {
                $requete = $requete->andWhere('transferArrival.date >= :dateStart AND transferArrival.date <= :dateEnd' )
                                    ->setParameter('dateStart', $dateStart->format('Y-m-d'))
                                    ->setParameter('dateEnd', $dateEnd->format('Y-m-d'))
                ;
            } else {
                    $requete = $requete->andWhere('transferArrival.date >= :dateStart and transferArrival.date <= :dateEnd')->setParameter('dateStart', $dateStart);
                    $requete = $requete->orWhere('transferArrival.date <= :dateStart and transferArrival.date >= :dateEnd ')
                    ->setParameter('dateStart', $dateStart)->setParameter('dateEnd',  $dateEnd);
            }
        }
        elseif ($natureTransfer == 2){
            $requete = $requete->leftJoin('App\Entity\TransferInterHotel', 'transferInterHotel', 'WITH', 'c.id = transferInterHotel.customerCard')
            ->leftJoin('App\Entity\AirportHotel', 'airportHotel', 'WITH', 'airportHotel.id = transferInterHotel.fromStart OR airportHotel.id = transferInterHotel.toArrival');
            if ($customerPresence == 2) {
                $requete = $requete->andWhere('transferInterHotel.date >= :dateStart AND transferInterHotel.date <= :dateEnd' )
                                    ->setParameter('dateStart', $dateStart->format('Y-m-d'))
                                    ->setParameter('dateEnd', $dateEnd->format('Y-m-d'))
                ;
            } else {
                    $requete = $requete->andWhere('transferInterHotel.date >= :dateStart and transferInterHotel.date <= :dateEnd')->setParameter('dateStart', $dateStart);
                    $requete = $requete->orWhere('transferInterHotel.date <= :dateStart and transferInterHotel.date >= :dateEnd ')
                    ->setParameter('dateStart', $dateStart)->setParameter('dateEnd',  $dateEnd);

            }
        }
        else{
            $requete = $requete->leftJoin('App\Entity\TransferDeparture', 'transferDeparture', 'WITH', 'c.id = transferDeparture.customerCard')
            ->leftJoin('App\Entity\AirportHotel', 'airportHotel', 'WITH', 'airportHotel.id = transferDeparture.fromStart OR airportHotel.id = transferDeparture.toArrival');
            if ($customerPresence == 2) {
                $requete = $requete->andWhere('transferDeparture.date >= :dateStart AND transferDeparture.date <= :dateEnd' )
                                    ->setParameter('dateStart', $dateStart->format('Y-m-d'))
                                    ->setParameter('dateEnd', $dateEnd->format('Y-m-d'))
                ;
            } else {
            
                    $requete = $requete->andWhere('transferDeparture.date >= :dateStart and transferDeparture.date <= :dateEnd ')
                    ->setParameter('dateStart', $dateStart)->setParameter('dateEnd',  $dateEnd);
                    $requete = $requete->orWhere('transferDeparture.date <= :dateStart and transferDeparture.date >= :dateEnd ')
                    ->setParameter('dateStart', $dateStart)->setParameter('dateEnd',  $dateEnd);
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

        // traitement du numéro de vol (est associé a la nature du transfer)
        if ($flightNumber != 'all') {
            if ($natureTransfer == "all") {
                $requete = $requete->andWhere('transferArrival.flightNumber LIKE :transferArrival 
                                OR transferDeparture.flightNumber LIKE :transferDeparture')
                                ->setParameter('transferArrival', '%'.$flightNumber.'%')
                                ->setParameter('transferDeparture', '%'.$flightNumber.'%');
            } elseif ($natureTransfer == 1) {
                $requete = $requete->andWhere('transferArrival.flightNumber LIKE :transferArrival')->setParameter('transferArrival', '%'.$flightNumber.'%');
            } elseif ($natureTransfer == 3) {
                $requete = $requete->andWhere('transferDeparture.flightNumber LIKE :transferDeparture')->setParameter('transferDeparture', '%'.$flightNumber.'%');
            }
            elseif($natureTransfer == 2) {
                $requete = $requete->andWhere('transferInterHotel.flightNumber LIKE :transferInterHotel')->setParameter('transferInterHotel', '%'.$flightNumber.'%');
            }
        }

        $requete = $requete ->getQuery()->getResult();

        return $requete;

    } 


    // team Manager

    /**
     * @return CustomerCard[] Returns an array of CustomerCard objects by staff and meeting date (day) + hotel and agency 
     * Attribution des représentants
     */
    public function findByForAttribbutionRep($date, $hotel, $agency): array
    {

        return $this->createQueryBuilder('c')
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->andWhere('c.staff is null')
            ->andWhere('c.meetingAt = :date')
            ->andWhere('transferArrival.toArrival = :hotel')
            ->andWhere('c.agency = :agency')
            ->setParameter('date', $date)
            ->setParameter('hotel', $hotel)
            ->setParameter('agency', $agency)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CustomerCard[] Returns an sum of CustomerCards Adults pax
     * Attribution des représentants
     */
    public function countPaxAdultsAttribbutionRep($date, $hotel, $agency)
    {

        return $this->createQueryBuilder('c')
            ->select('sum(c.adultsNumber)')
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->andWhere('c.staff is null')
            ->andWhere('c.meetingAt = :date')
            ->andWhere('transferArrival.toArrival = :hotel')
            ->andWhere('c.agency = :agency')
            ->setParameter('date', $date)
            ->setParameter('hotel', $hotel)
            ->setParameter('agency', $agency)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CustomerCard[] Returns an sum of CustomerCards children pax
     * Attribution des représentants
     */
    public function countPaxChildrenAttribbutionRep($date, $hotel, $agency) 
    {

        return $this->createQueryBuilder('c')
            ->select('sum(c.childrenNumber)')
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->andWhere('c.staff is null')
            ->andWhere('c.meetingAt = :date')
            ->andWhere('transferArrival.toArrival = :hotel')
            ->andWhere('c.agency = :agency')
            ->setParameter('date', $date)
            ->setParameter('hotel', $hotel)
            ->setParameter('agency', $agency)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CustomerCard[] Returns the sum of CustomerCards Babies pax
     * Attribution des représentants
     */
    public function countPaxBabiesAttribbutionRep($date, $hotel, $agency) 
    {
        return $this->createQueryBuilder('c')
            ->select('sum(c.babiesNumber)')
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->andWhere('c.staff is null')
            ->andWhere('c.meetingAt = :date')
            ->andWhere('transferArrival.toArrival = :hotel')
            ->andWhere('c.agency = :agency')
            ->setParameter('date', $date)
            ->setParameter('hotel', $hotel)
            ->setParameter('agency', $agency)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CustomerCard[] Returns an array of customersCards at this choosen date grouped by staff, agency and arrival hotel
     * This return the first customerCard of each groupment
     * Attribution des représentants
     */
    public function regroupmentByDayStaffAgencyAndHotel($date) :array
    {

       
        return $this->createQueryBuilder('c')
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->andWhere('c.meetingAt >= :dateStart')
            ->andWhere('c.meetingAt <= :dateEnd')
            ->andWhere('c.staff is not null')
            ->setParameter('dateStart', $date->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $date->format('Y-m-d 23:59:59'))
            ->groupBy('c.staff, c.agency ,transferArrival.toArrival')      
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CustomerCard[] Returns an array of each date for customers without rep 
     * Attribution des représentants
     */
    public function datesForCustomersWithoutRep() :array
    {

        return $this->createQueryBuilder('c')
            ->innerJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->select('transferArrival.date')
            ->where('c.staff is null')
            ->groupBy('transferArrival.date')      
            ->getQuery()
            ->getResult()
        ;
    }




    /**
     * @return CustomerCard[] Returns an sum of CustomerCards pax by age and date
     * nombre de pax attribués pour un rep à ce jour
     */
    public function staffPaxAdultsByDate($staff,$date, $age)
    {
        $date = $date->format('Y-m-d');
        $requete = $this->createQueryBuilder('c');

        if ($age == "adults") { $requete = $requete->select('sum(c.adultsNumber)');} 
        elseif ($age == "children") { $requete = $requete->select('sum(c.childrenNumber)');} 
        else { $requete = $requete->select('sum(c.babiesNumber)') ;}
       
        return 
            $requete
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'c.id = transferArrival.customerCard')
            ->andWhere('transferArrival.date = :date')
            ->andWhere('c.staff = :staff')
            ->setParameter('date', $date)     
            ->setParameter('staff', $staff)     
            ->getQuery()
            ->getSingleScalarResult()
        ;
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
