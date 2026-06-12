<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Food;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FoodFixtures extends Fixture implements DependentFixtureInterface
{
    public const FOOD_NB_TUPLES = 20;
    public const FOOD_REFERENCE = 'food';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::FOOD_NB_TUPLES; $i++) {

            $food = (new Food())
                ->setTitle("Food $i")
                ->setDescription($faker->paragraph())
                ->setPrice(random_int(8, 35))
                ->setCreatedAt(new DateTimeImmutable());

            $nbCategories = random_int(1, 3);

            for ($j = 1; $j <= $nbCategories; $j++) {

                $food->addCategory(
                    $this->getReference(
                        CategoryFixtures::CATEGORY_REFERENCE . random_int(1, CategoryFixtures::CATEGORY_NB_TUPLES),
                        Category::class
                    )
                );
            }

            $manager->persist($food);

            $this->addReference(
                self::FOOD_REFERENCE . $i,
                $food
            );
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}