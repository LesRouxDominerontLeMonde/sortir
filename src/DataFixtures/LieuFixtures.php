<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        // $product = new Product();
        // $manager->persist($product);
        for($i=0; $i<10; $i++){
            $lieu = new Lieu();
            $lieu->setVille($this->getReference('ville_'.random_int(0, 4)))
            ->setNom('Nom_lieu'.$i)
            ->setRue($faker->streetAddress())
            ->setLatitude($faker->latitude(43.18, 50.06))
            ->setLongitude($faker->longitude(-6.1, 3.1));
            $manager->persist($lieu);
            $this->addReference('lieu_'.$i, $lieu);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [VilleFixtures::class];
    }
}
