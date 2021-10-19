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
            $user->setNom($faker->lastName());
            $user->setRoles(['ROLE_USER']);
            $user->setActif(true);
            $user->setEmail($faker->email());
            $password = $this->encoder->encodePassword($user, "admin");
            $user->setPassword($password);
            $user->setAdmin(false);
            $user->setPrenom($faker->firstName());
            $user->setTelephone(trim($faker->phoneNumber()));
            $user->setUsername($user->getPrenom()."_".mt_rand(1,10));

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
