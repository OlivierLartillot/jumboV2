<?php

namespace App\Repository;

use App\Entity\TransferArrival;
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
        
        // TODO VOUCHER NUMBER !!!
        
        $requete = $this->createQueryBuilder('ta');
        $requete = $requete->andWhere('ta.date = :date')->setParameter('date', $date); 
        if ($airport != 'all') {
            $requete = $requete->andWhere('ta.fromStart = :airport')->setParameter('airport', $airport);
        }
        if ($flightNumber != '') {
        $requete = $requete->andWhere('ta.flightNumber LIKE :flightNumber')->setParameter('flightNumber', '%'.$flightNumber.'%');
        }
        if ($voucherNumber != '') {
            $requete = $requete->andWhere('ta.voucherNumber LIKE :voucherNumber')->setParameter('voucherNumber', '%'.$voucherNumber.'%');
        }
        
        $requete = $requete->orderBy('ta.id', 'ASC')
        ->getQuery()
        ->getResult()
        ;

        return $requete;
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
