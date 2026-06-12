<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RestaurantFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 20; $i++) {
            $restaurant = (new Restaurant())
                ->setName(name: "Restaurant $i")
                ->setDescription(description: "Description $i")
                ->setAmOpeningTime([])
                ->setPmOpeningTime([])
                ->setMaxGuest(random_int(10, 50))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($restaurant);

            $this->addReference(
                self::RESTAURANT_REFERENCE . $i,
                $restaurant
            );
        }

        $manager->flush();
    }
}