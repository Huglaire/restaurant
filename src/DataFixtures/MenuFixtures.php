<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Menu;
use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MenuFixtures extends Fixture implements DependentFixtureInterface
{
    public const MENU_NB_TUPLES = 20;
    public const MENU_REFERENCE = 'menu';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::MENU_NB_TUPLES; $i++) {

            $menu = (new Menu())
                ->setTitle("Menu $i")
                ->setDescription($faker->paragraph())
                ->setPrice(random_int(22, 36))
                ->setRestaurant(
                    $this->getReference(
                        RestaurantFixtures::RESTAURANT_REFERENCE . random_int(
                            1,
                            RestaurantFixtures::RESTAURANT_NB_TUPLES
                        ),
                        Restaurant::class
                    )
                )
                ->setCreatedAt(new DateTimeImmutable());

            // Ajout de 1 à 3 catégories aléatoires
            $nbCategories = random_int(1, 3);

            for ($j = 1; $j <= $nbCategories; $j++) {

                $menu->addCategory(
                    $this->getReference(
                        CategoryFixtures::CATEGORY_REFERENCE . random_int(
                            1,
                            CategoryFixtures::CATEGORY_NB_TUPLES
                        ),
                        Category::class
                    )
                );
            }

            $manager->persist($menu);

            $this->addReference(
                self::MENU_REFERENCE . $i,
                $menu
            );
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            RestaurantFixtures::class,
            CategoryFixtures::class,
        ];
    }
}