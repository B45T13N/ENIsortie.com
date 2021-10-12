<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i = 1; $i <= 10 ; $i++) {
            $user = new User();
            $user->setCampus($this->getReference(Campus::class.'_'.mt_rand(1,5)));
            $user->setNom($faker->name());
            $user->setRoles(['ROLE_USER']);
            $user->setActif(true);
            $user->setEmail($faker->email());
            $password = $this->encoder->encodePassword($user, "admin");
            $user->setPassword($password);
            $user->setAdmin(true);
            $user->setPrenom($faker->firstName());
            $user->setTelephone(0101010101);
            $user->setUsername($user->getPrenom().'_'.$user->getNom());

            $manager->persist($user);
            $this->addReference(User::class.'_'.$i, $user);
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
