<?php

namespace App\Repository;

use App\Entity\TransferDeparture;
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
