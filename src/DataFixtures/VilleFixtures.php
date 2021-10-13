<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i = 1; $i <= 10 ; $i++) {
            $ville = new Ville();
            $ville->setNom($faker->city());
            $ville->setCodePostal("25000");
            $manager->persist($ville);
            $this->addReference(Ville::class.'_'.$i, $ville);
        }
        $manager->flush();
    }
}
