<?php

namespace App\DataFixtures;

use App\Entity\Category;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_NB_TUPLES = 20;
    public const CATEGORY_REFERENCE = 'category';

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::CATEGORY_NB_TUPLES; $i++) {

            $category = (new Category())
                ->setTitle("Category $i")
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($category);

            $this->addReference(
                self::CATEGORY_REFERENCE . $i,
                $category
            );
        }

        $manager->flush();
    }
}