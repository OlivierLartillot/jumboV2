<?php

namespace App\Repository;

use App\Entity\TransferVehicleInterHotel;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferVehicleInterHotel>
 *
 * @method TransferVehicleInterHotel|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferVehicleInterHotel|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferVehicleInterHotel[]    findAll()
 * @method TransferVehicleInterHotel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferVehicleInterHotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferVehicleInterHotel::class);
    }

    public function save(TransferVehicleInterHotel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferVehicleInterHotel $entity, bool $flush = false): void
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
    public function findCustomerCardsBydatesAndCompanies($dateStart, $dateEnd, $company): array
    {


        $dateStart = new DateTimeImmutable($dateStart);
        $dateEnd = new DateTimeImmutable($dateEnd);

        $requete = $this->createQueryBuilder('tih')
            ->leftJoin('App\Entity\CustomerCard', 'customerCard', 'WITH', 'customerCard.id = tih.customerCard')
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
                ->andWhere('tih.transportCompany = :company') 
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
//     * @return TransferVehicleInterHotel[] Returns an array of TransferVehicleInterHotel objects
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

//    public function findOneBySomeField($value): ?TransferVehicleInterHotel
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
