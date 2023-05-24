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
                $lname = $faker->lastName();
                $mail = preg_replace('/\s/', '_', $lname.$fname)."@".$faker->safeEmailDomain();
                $users->setCampus($this->getReference('campus_'.$j));
                // setPassword contains a hash for the password 'password'
                $users->setActif(true)
                    ->setEmail($mail)
                    ->setName($lname)
                    ->setFirstname($fname)
                    ->setPseudo("pseudo_".$i."_$j")
                    ->setPassword('$2y$13$jl4KFkN7n78oToXKMVWELuaIcjAp6/7sfuSLOnG6dqogm1YCsUv9y');
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
