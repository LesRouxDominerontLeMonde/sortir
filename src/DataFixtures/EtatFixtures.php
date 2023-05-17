<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $i = 0;
        foreach ([
            'Créée',
            'Ouverte',
            'Clôturée',
            'En cours',
            'Passée',
            'Annulée',
                 ] as $v){
            $etat = new Etat();
            $etat->setLibelle($v);
            $manager->persist($etat);
            $this->addReference('etat_'.$i, $etat);
            $i++;
        }
        $manager->flush();
    }
}
