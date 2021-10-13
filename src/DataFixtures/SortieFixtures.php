<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i = 1; $i <= 10 ; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->unique->word());
            $sortie->setDescription($faker->realText());
            $sortie->setCampus($this->getReference(Campus::class.'_'.mt_rand(1,5)));
            $sortie->setDate($faker->dateTimeBetween('-1 months', 'now'));
            $sortie->setDateLimite($faker->dateTimeBetween($sortie->getDate(), 'now'));
            $sortie->setDuree($faker->randomNumber(3, false));
            $sortie->setEtat($this->getReference(Etat::class.'_'.mt_rand(1,6)));
            $sortie->setLieu($this->getReference(Lieu::class.'_'.mt_rand(1,10)));
            $sortie->setNombreInscriptionsMax($faker->randomNumber(2, false));
            $sortie->setOrganisateur($this->getReference(User::class.'_'.mt_rand(1,10)));
            $manager->persist($sortie);
            $this->addReference(Sortie::class.'_'.$i, $sortie);
        }
        $manager->flush();

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CampusFixtures::class,
            LieuFixtures::class,
            EtatFixtures::class,

        ];
    }
}
