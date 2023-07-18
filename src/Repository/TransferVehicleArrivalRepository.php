<?php

namespace App\Repository;

use App\Entity\TransferVehicleArrival;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferVehicleArrival>
 *
 * @method TransferVehicleArrival|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferVehicleArrival|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferVehicleArrival[]    findAll()
 * @method TransferVehicleArrival[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferVehicleArrivalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferVehicleArrival::class);
    }

    public function save(TransferVehicleArrival $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferVehicleArrival $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return TransferJoan[] Returns an array of distinct transport_company  
     */
    public function transportCompanyList(): array
    {

        return $this->createQueryBuilder('t')
            ->select('t.transportCompany')->distinct()
            ->orderBy('t.transportCompany', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }




    /**
     * @return CustomerCard[] Returns an array of CustomerCard objects by staff and meeting date (day) + hotel and agency 
     * Attribution des représentants
     */
/*     public function findCustomerCardsBydatesAndCompanies($dateStart, $dateEnd, $company): array
    {


        $dateStart = new DateTimeImmutable($dateStart);
        $dateEnd = new DateTimeImmutable($dateEnd);

        $requete = $this->createQueryBuilder('ta')
            ->leftJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = ta.customerCard')
            ->leftJoin('App\Entity\TransferArrival', 'transferArrival', 'WITH', 'customerCard.id = transferArrival.customerCard')
            ->leftJoin('App\Entity\TransferInterHotel', 'transferInterHotel', 'WITH', 'customerCard.id = transferInterHotel.customerCard')
            ->leftJoin('App\Entity\TransferDeparture', 'transferDeparture', 'WITH', 'customerCard.id = transferDeparture.customerCard')
            ->andWhere('transferArrival.date >= :dateStart and transferArrival.date <= :dateEnd')
            ->orWhere('transferInterHotel.date >= :dateStart and transferInterHotel.date <= :dateEnd')
            ->orWhere('transferDeparture.date >= :dateStart and transferDeparture.date <= :dateEnd')
            ->setParameter('dateStart', $dateStart->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $dateEnd->format('Y-m-d 23:59:59'));

        if ($company != 'all') {
            $requete = $requete
                ->andWhere('ta.transportCompany = :company') 
            ->setParameter('company', $company)
                
                ;

        }

        $requete = $requete
            ->getQuery()
            ->getResult()
        ;

        return $requete;

    } */

    /**
     * @return CustomerCard[] Returns an array of CustomerCard objects by staff and meeting date (day) + hotel and agency 
     * Attribution des représentants
     */
        public function findCustomerCardsBydatesAndCompanies($dateStart, $dateEnd, $company): array
    {

        $dateStart = new DateTimeImmutable($dateStart);
        $dateEnd = new DateTimeImmutable($dateEnd);

        $requete = $this->createQueryBuilder('ta')
            ->andWhere('ta.date >= :dateStart and ta.date <= :dateEnd')
            ->setParameter('dateStart', $dateStart->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $dateEnd->format('Y-m-d 23:59:59'));

        if ($company != 'all') {
            $requete = $requete
                ->andWhere('ta.transportCompany = :company') 
            ->setParameter('company', $company)
                
                ;

        }

        $requete = $requete
            ->getQuery()
            ->getResult()
        ;

        return $requete;

    }








//    /**
//     * @return TransferVehicleArrival[] Returns an array of TransferVehicleArrival objects
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

//    public function findOneBySomeField($value): ?TransferVehicleArrival
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
