<?php

namespace App\DataFixtures;

use App\Entity\{Picture, Restaurant};
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    public const PICTURE_NB_TUPLES =20;
    public const PICTURE_REFERENCE = "picture";

    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create('fr_FR');
        $slugger = new AsciiSlugger();

        for($i = 1; $i <= self::PICTURE_NB_TUPLES; $i++) {
        $title = $faker->sentence(3);
        $picture = (new Picture())
            ->setTitle($title)
            ->setSlug(strtolower($slugger->slug($title)))
            ->setRestaurant($this->getReference(RestaurantFixtures::RESTAURANT_REFERENCE . random_int(1, 20),
        Restaurant::class
    )
)
            ->setCreatedAt(new DateTimeImmutable());

        $manager->persist($picture);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [RestaurantFixtures::class];
    }
}
