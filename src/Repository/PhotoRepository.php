<?php

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 *
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[]    findAll()
 * @method Photo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function add(Photo $entity, bool $flush = false): void
    {
        $this->disablePrevious($entity);
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Photo $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        if($entity->isActive()){
            $user = $entity->getUtilisateur()->getId();
            //$newPhoto = $this->findOnePhoto($user);
            $newPhoto = $this->findAnotherPhoto($entity);
            if ($newPhoto)
            {
                $sql = 'UPDATE photos SET active = 1 WHERE id = :id';
                $cnx = $em->getConnection();
                $stmt = $cnx->prepare($sql);
                $stmt->executeQuery(['id' => $newPhoto->getId()]);
            }
        }

        $em->remove($entity);

        if ($flush) {
            $em->flush();
        }
    }

    private function disablePrevious(Photo $entity): void
    {
        $user = $entity->getUtilisateur()->getId();
        $sql = 'UPDATE photos SET active = 0 WHERE utilisateur_id = :user';
        $cnx = $this->getEntityManager()->getConnection();
        $stmt = $cnx->prepare($sql);
        $stmt->executeQuery(['user' => $user]);
    }


//    /**
//     * @return Photo[] Returns an array of Photo objects
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

//    public function findOneBySomeField($value): ?Photo
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findCurrentPhoto(int $id): ?Photo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :user')
            ->andWhere('p.active = :active')
            ->setParameter('user', $id)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOnePhoto(int $id): ?Photo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :user')
            ->setParameter('user', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAnotherPhoto(Photo $entity): ?Photo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :user')
            ->andWhere('p.id != :id')
            ->setParameter('user', $entity->getUtilisateur()->getId())
            ->setParameter('id', $entity->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
