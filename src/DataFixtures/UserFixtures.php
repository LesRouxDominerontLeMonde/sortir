<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        // $product = new Product();
        // $manager->persist($product);
        for($i=0; $i<5; $i++){
            for($j=0;$j<5;$j++) {
                $users =new User();
                $fname = $faker->firstName();
                $name = $faker->name();
                $mail = "${name}.${fname}@".$faker->safeEmailDomain();
                $users->setCampus($this->getReference('campus_'.$j));
                $users->setActif(true)
                    ->setEmail($mail)
                    ->setName($name)
                    ->setFirstname($fname)
                    ->setPseudo("pseudo_$i")
                    ->setPassword('password');
                $manager->persist($users);
                $this->addReference('users_'.$i.'_c_'.$j, $users);
            }

        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CampusFixtures::class,
        ];
    }
}
