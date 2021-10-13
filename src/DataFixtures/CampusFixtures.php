<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i = 1; $i <= 5 ; $i++) {
            $campus = new Campus();
            $campus->setNom($faker->city());
            $manager->persist($campus);
            $this->addReference(Campus::class.'_'.$i, $campus);
        }
        $manager->flush();
    }
}
