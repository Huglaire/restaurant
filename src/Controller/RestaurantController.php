<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/restaurant', name: 'app_api_restaurant_')]
class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $repository,
    ) {
    }

    #[Route('/', name: 'new', methods: ['POST'])]
    public function new(): JsonResponse
    {
        $this->manager->persist();
        $this->manager->flush();

        return new JsonResponse(
            data: null,
            status: Response::HTTP_CREATED,
            json: true
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if ($restaurant) {
            return new JsonResponse(
                data: null,
                status: Response::HTTP_OK,
                json: true
            );
        }

        return new JsonResponse(
            data: null,
            status: Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if ($restaurant) {
            $this->manager->flush();

            return new JsonResponse(
                data: null,
                status: Response::HTTP_NO_CONTENT
            );
        }

        return new JsonResponse(
            data: null,
            status: Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if ($restaurant) {
            $this->manager->remove($restaurant);
            $this->manager->flush();

            return new JsonResponse(
                data: null,
                status: Response::HTTP_NO_CONTENT
            );
        }

        return new JsonResponse(
            data: null,
            status: Response::HTTP_NOT_FOUND
        );
    }
}