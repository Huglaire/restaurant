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

        $servicesMidi = [
            ['open' => '11:30', 'close' => '14:00'],
            ['open' => '12:00', 'close' => '14:30'],
            ['open' => '12:00', 'close' => '15:00'],
        ];

        $servicesSoir = [
            ['open' => '18:30', 'close' => '22:00'],
            ['open' => '19:00', 'close' => '22:30'],
            ['open' => '19:30', 'close' => '23:00'],
        ];

        for ($i = 1; $i <= self::RESTAURANT_NB_TUPLES; $i++) {

            $restaurant = (new Restaurant())
                ->setName('Restaurant ' . $faker->lastName())
                ->setDescription($faker->paragraphs(3, true))
                ->setAmOpeningTime(
                    $faker->randomElement($servicesMidi)
                )
                ->setPmOpeningTime(
                    $faker->randomElement($servicesSoir)
                )
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