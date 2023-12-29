<?php

namespace App\Repository;

use App\Entity\TransferDeparture;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferDeparture>
 *
 * @method TransferDeparture|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferDeparture|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferDeparture[]    findAll()
 * @method TransferDeparture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferDepartureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferDeparture::class);
    }

    public function save(TransferDeparture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferDeparture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return TransferDeparture[] Returns an array of TransferDeparture objects
     */
    public function findByUserAndDate($user, $date): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('App\Entity\CustomerCard', 'c', 'WITH', 'c.id = t.customerCard')
            ->andWhere('c.staff = :user')
            ->andWhere('t.date = :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
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
     * retourne un tableau des Départs multiples pour un meme compte (customer_card)
     */
    public function findMultiplesDepartures() :array
    {
        $tableauFinalDesDoublons = [];
        $results = $this->createQueryBuilder('t')
                    ->select('t as transferDeparture', 'count(t.id) as count', 't.duplicateIgnored')
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
     * @return TransferDeparture Returns an array of CustomerCard objects by the day, the nature transfer and service number
     *
     */
    /* 
    public function findByDateAndCustomerCard($customerCard, $date): array
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
     * @return TransferDeparture Returns an array of TransferDeparture objects by the day, and the staff
     * 
     */ 
    public function finfByStaffAndDate($date, $staff): array
    { 

        return $this->createQueryBuilder('d')
                    /* ->innerJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = d.customerCard') */
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
     * @return TransferDeparture[] Returns an array of CustomerCard objects by staff and meeting date (day) + hotel and agency 
     * Attribution des représentants
     */
    public function findDeparturesBydatesAndCompanies($dateStart, $dateEnd, $company): array
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

        $requete = $requete
            ->getQuery()
            ->getResult()
        ;

        return $requete;

    }
//    public function findOneBySomeField($value): ?TransferDeparture
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
