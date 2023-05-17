<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create('fr_FR');
        for($i = 0; $i < 5; $i++){
            $city = new Ville();
            $city->setNom("ville_$i");
            $city->setCodePostal("0000$i");
            $manager->persist($city);
            $this->addReference('ville_'.$i, $city);
        }
        $manager->flush();
    }
}
