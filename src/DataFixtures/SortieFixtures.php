<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create('fr_FR');

        // Compteur de campus
        for ($c=0; $c<5; $c++) {
            $campus = $this->getReference("campus_$c");
            // compteur de lieu
            for ($l = 0; $l < 10; $l++) {
                $lieu = $this->getReference("lieu_$l");
                $rnd_etat = random_int(0, 5);
                $sortie = new Sortie();
                $sortie->setNom($faker->slug())
                    ->setCampusOrigine($campus)
                    ->setArchivee(false)
                    ->setInscriptionsMax(random_int(5, 10))
                    ->setLieu($lieu)
                    ->setDebut($faker->dateTimeBetween('now', '3 months', 'Europe/Paris'))
                    ->setFinInscription($faker->dateTimeInInterval)
                    ->setDuree(new \DateInterval('P2D'))
                    ->setOrganisateur($this->getReference("users_0_c_$c"))
                    ->addParticipant($this->getReference("users_0_c_$c"))
                    ->addParticipant($this->getReference("users_2_c_$c"))
                    ->setEtat($this->getReference("etat_${rnd_etat}"))
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUpdatedAt(new \DateTimeImmutable());

                $manager->persist($sortie);

            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LieuFixtures::class,
            UserFixtures::class,
            CampusFixtures::class,
            EtatFixtures::class
        ];
    }
}