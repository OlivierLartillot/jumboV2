<?php

namespace App\Repository;

use App\Entity\TransferVehicleDeparture;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferVehicleDeparture>
 *
 * @method TransferVehicleDeparture|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferVehicleDeparture|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferVehicleDeparture[]    findAll()
 * @method TransferVehicleDeparture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferVehicleDepartureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferVehicleDeparture::class);
    }

    public function save(TransferVehicleDeparture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TransferVehicleDeparture $entity, bool $flush = false): void
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

        $requete = $this->createQueryBuilder('td')
            ->andWhere('td.date >= :dateStart and td.date <= :dateEnd')
            ->setParameter('dateStart', $dateStart->format('Y-m-d 00:00:00'))
            ->setParameter('dateEnd', $dateEnd->format('Y-m-d 23:59:59'));

        if ($company != 'all') {
            $requete = $requete
                ->andWhere('td.transportCompany = :company') 
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
//     * @return TransferVehicleDeparture[] Returns an array of TransferVehicleDeparture objects
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

//    public function findOneBySomeField($value): ?TransferVehicleDeparture
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
