<?php

namespace App\Controller;

use App\Entity\Food;
use App\Repository\FoodRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/food', name: 'app_api_food_')]
class FoodController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private FoodRepository $repository)
    {
    }

    #[Route('/', name: 'new', methods: ['POST'])]
    public function new(): Response
    {
        $food = new Food();
        $food->setTitle('Viande');
        $food->setDescription('Viande de boeuf marinée');
        $food->setPrice(16);
        $food->setCreatedAt(new DateTimeImmutable());

        // Tell Doctrine you want to (eventually) save the food (no queries yet)
        $this->manager->persist($food);
        // Actually executes the queries (i.e. the INSERT query)
        $this->manager->flush();

        return $this->json(
            ['message' => "Food resource created with id: {$food->getId()}"],
            Response::HTTP_CREATED,
        );
    }


    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No food found for id: {$id}");
        }

        return $this->json(
            ['message' => "A food was found : {$food->getTitle()} for id: {$food->getId()}"]
        );
    }


    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No food found for {$id} id");
        }

        $food->setTitle('food name updated');
        $this->manager->flush();

        return $this->redirectToRoute('app_api_food_show', ['id' => $food->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $food = $this->repository->findOneBy(['id' => $id]);
        if (!$food) {
            throw $this->createNotFoundException("No food found for {$id} id");
        }

        $this->manager->remove($food);
        $this->manager->flush();

        return $this->json(['message' => "food resource deleted"], Response::HTTP_OK);
    }

}

