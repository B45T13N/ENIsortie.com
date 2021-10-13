<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $etat = new Etat();
        $etat->setLibelle("Créée");
        $manager->persist($etat);
        $this->addReference(Etat::class.'_'."1", $etat);

        $etat2 = new Etat();
        $etat2->setLibelle("Ouverte");
        $manager->persist($etat2);
        $this->addReference(Etat::class.'_'."2", $etat2);

        $etat3 = new Etat();
        $etat3->setLibelle("Clôturée");
        $manager->persist($etat3);
        $this->addReference(Etat::class.'_'."3", $etat3);

        $etat4 = new Etat();
        $etat4->setLibelle("Activité en cours");
        $manager->persist($etat4);
        $this->addReference(Etat::class.'_'."4", $etat4);

        $etat5 = new Etat();
        $etat5->setLibelle("Passée");
        $manager->persist($etat5);
        $this->addReference(Etat::class.'_'."5", $etat5);

        $etat6 = new Etat();
        $etat6->setLibelle("Annulée");
        $manager->persist($etat6);
        $this->addReference(Etat::class.'_'."6", $etat6);

        $manager->flush();
    }
}
