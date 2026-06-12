<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RestaurantFixtures extends Fixture
{
    public const RESTAURANT_NB_TUPLES = 20;
    public const RESTAURANT_REFERENCE = 'restaurant';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::RESTAURANT_NB_TUPLES; $i++) {

            $restaurant = (new Restaurant())
                ->setName($faker->company())
                ->setDescription($faker->paragraph(3))
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