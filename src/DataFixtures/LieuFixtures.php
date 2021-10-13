<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i = 1; $i <= 10 ; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->city());
            $lieu->setRue($faker->streetName());
            $lieu->setLatitude($faker->latitude());
            $lieu->setLongitude($faker->longitude());
            $lieu->setVille($this->getReference(Ville::class.'_'.mt_rand(1,10)));
            $manager->persist($lieu);
            $this->addReference(Lieu::class.'_'.$i, $lieu);
        }

        $manager->flush();
    }


    public function getDependencies()
    {
        return [
            VilleFixtures::class,
            ];
    }
}
