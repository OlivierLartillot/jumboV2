<?php

namespace App\Repository;

use App\Entity\TransferVehicleArrival;
use App\Entity\TransportCompany;
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
     * @return TransferVehicleArrival[] Returns an array 
     * 
     */
    public function findVehicleArrivalsBydatesAndCompanies($dateStart, $dateEnd, $company, $area, $type): array
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
            ->setParameter('company', $company);
        }
        if ($area != 'all') {
            $requete = $requete
            ->andWhere('ta.area = :area') 
            ->setParameter('area', $area);
        }
        if ($type != 'all') {
            $requete = $requete
            ->andWhere('ta.isCollective = :isCollective') 
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
    public function findTransferVehicleArrivalAreas(): array
    {
        return $this->createQueryBuilder('ta')
                    ->select('ta.area')
                    ->distinct()
                    ->getQuery()
                    ->getResult()
                ;
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
