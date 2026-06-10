<?php

namespace App\DataFixtures;

use App\Entity\{Picture, Restaurant};
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 20; $i++) {
        $picture = (new Picture())
            ->setTitle(title: "Image $i")
            ->setSlug(slug: "slug-article-title $i")
            ->setRestaurant($this->getReference("restaurant" . random_int(1, 20),
        Restaurant::class
    )
)
            ->setCreatedAt(new DateTimeImmutable());

        $manager->persist($picture);
        }
        $manager->flush();
    }
    #[Override]
    public function getDependencies(): array
    {
        return [RestaurantFixtures::class];
    }
}
