<?php

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 *
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[]    findAll()
 * @method Photo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function add(Photo $entity, bool $flush = false): void
    {
        // Avant d'ajouter une nouvelle photo de profil, on désactive la courante
        $this->disablePrevious($entity);
        // Puis on met le flag active à true et on persiste la nouvelle photo
        $entity->setActive(true);
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Photo $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        // Si on supprime la photo active (pour si maj de la gestion des profils)
        if($entity->isActive()){
            // On recherche une autre photo du même profil
            $newPhoto = $this->findAnotherPhoto($entity);
            // Si on l'a trouvé, on l'active et on persiste
            if ($newPhoto)
            {
                $newPhoto->setActive(true);
                $em->persist($newPhoto);
            }
        }
        // Puis on supprime
        $em->remove($entity);

        if ($flush) {
            $em->flush();
        }
    }

    private function disablePrevious(Photo $entity, bool $flush = false): void
    {
        $entity->setActive(false);
        $this->getEntityManager()->persist($entity);
        if($flush){
            $this->getEntityManager()->flush();
        }
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
    /**
     * @throws NonUniqueResultException
     */
    public function findCurrentPhoto(int $id): ?Photo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :user')
            ->andWhere('p.active = :active')
            ->setParameter('user', $id)
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOnePhoto(int $id): ?Photo
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur_id = :user')
            ->setParameter('user', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
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
