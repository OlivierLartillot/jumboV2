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
