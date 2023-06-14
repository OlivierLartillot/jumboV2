<?php

namespace App\Repository;

use App\Entity\PredefinedCommentsMessages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PredefinedCommentsMessages>
 *
 * @method PredefinedCommentsMessages|null find($id, $lockMode = null, $lockVersion = null)
 * @method PredefinedCommentsMessages|null findOneBy(array $criteria, array $orderBy = null)
 * @method PredefinedCommentsMessages[]    findAll()
 * @method PredefinedCommentsMessages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PredefinedCommentsMessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PredefinedCommentsMessages::class);
    }

    public function save(PredefinedCommentsMessages $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PredefinedCommentsMessages $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PredefinedCommentsMessages[] Returns an array of PredefinedCommentsMessages objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PredefinedCommentsMessages
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
