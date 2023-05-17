<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        for($i=0; $i<5; $i++){
            $campus = new Campus();
            $campus->setNom("nom_du_campus_$i");
            $manager->persist($campus);
            $this->addReference('campus_'.$i, $campus);
        }
        $manager->flush();
    }
}
