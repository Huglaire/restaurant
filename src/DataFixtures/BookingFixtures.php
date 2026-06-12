<?php

namespace App\DataFixtures;

use App\Entity\{Booking, Restaurant, User};
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BookingFixtures extends Fixture implements DependentFixtureInterface
{
    public const BOOKING_NB_TUPLES = 20;
    public const BOOKING_REFERENCE = 'booking';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::BOOKING_NB_TUPLES; $i++) {

            $booking = (new Booking())
                ->setGuestNumber(random_int(1, 11))
                ->setOrderDate(new DateTime())
                ->setOrderHour(new DateTime())
                ->setAllergy("allergy $i")
                ->setRestaurant(
                    $this->getReference(
                        RestaurantFixtures::RESTAURANT_REFERENCE . random_int(1, RestaurantFixtures::RESTAURANT_NB_TUPLES),
                        Restaurant::class
                    )
                )
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($booking);

            $this->addReference(
                self::BOOKING_REFERENCE . $i,
                $booking
            );
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class,
        ];
    }
}