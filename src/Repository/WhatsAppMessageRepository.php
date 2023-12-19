<?php

namespace App\Repository;

use App\Entity\WhatsAppMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WhatsAppMessage>
 *
 * @method WhatsAppMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method WhatsAppMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method WhatsAppMessage[]    findAll()
 * @method WhatsAppMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WhatsAppMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WhatsAppMessage::class);
    }

//    /**
//     * @return WhatsAppMessage[] Returns an array of WhatsAppMessage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?WhatsAppMessage
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
