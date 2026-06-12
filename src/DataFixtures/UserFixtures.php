<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class UserFixtures extends Fixture
{
    public const USER_NB_TUPLES =20;
    public const USER_REFERENCE = "user";

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create('fr_FR');

        for($i = 1; $i <= self::USER_NB_TUPLES; $i++) {
        $user = (new User())
            ->setFirstName($faker->firstName())
            ->setLastName($faker->lastName())
            ->setGuestNumber(random_int(1, 10))
            ->setEmail($faker->email())
            ->setCreatedAt(new DateTimeImmutable())
            ->setRoles(["ROLE_USER"]);

        $user->setPassword($this->passwordHasher->hashPassword($user, plainPassword: "password$i"));

        $manager->persist($user);

        $this->addReference(
        self::USER_REFERENCE . $i,
        $user
);
        }
        $manager->flush();
    }
}
