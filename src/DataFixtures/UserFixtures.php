<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 20; $i++) {
        $user = (new User())
            ->setFirstName(firstName: "Firstname $i")
            ->setLastName(lastName: "Lastname $i")
            ->setGuestNumber(random_int(1, 10))
            ->setEmail(email: "email.$i@mail.com")
            ->setCreatedAt(new DateTimeImmutable())
            ->setRoles(["ROLE_USER"]);

        $user->setPassword($this->passwordHasher->hashPassword($user, plainPassword: "password$i"));

        $manager->persist($user);
        }
        $manager->flush();
    }
}
