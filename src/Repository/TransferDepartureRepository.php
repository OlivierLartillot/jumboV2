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
     * retourne le compte des Départs multiples  
     * cette requête sert sur le dashboard a savoir si il y a au moins un doublon a régler
     *  
     */
    public function countMultiplesDepartures():array 
    {

        return $this->createQueryBuilder('t')
            ->select(select: 't.id')
            ->where('t.duplicateIgnored = false')
            ->groupBy('t.customerCard')
            ->having('COUNT(t.customerCard) >= 2') 
            ->setMaxResults(1)
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
        return $this->createQueryBuilder('t')
                    ->select('t as transferDeparture')
                    ->where('t.duplicateIgnored = false')
                    ->groupBy('t.customerCard')
                    ->having('COUNT(t.customerCard) >= 2') 
                    ->getQuery()
                    ->getResult();

                    
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
    public function findDeparturesBydatesAndCompanies($dateStart, $dateEnd, $company, $area, $type): array
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
    public function findTransferDepartureAreas(): array
    {
        return $this->createQueryBuilder('t')
                    ->select('t.area')
                    ->distinct()
                    ->getQuery()
                    ->getResult()
                ;
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
