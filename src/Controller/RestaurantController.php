<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/restaurant', name: 'app_api_restaurant_')]
class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

#[Route('', name: 'new', methods: ['POST'])]

#[OA\Post(
    path: '/api/restaurant',
    summary: 'Créer un restaurant'
)]

#[OA\RequestBody(
    required: true,
    description: 'Données du restaurant à créer',
    content: new OA\JsonContent(
        type: 'object',
        required: [
            'name',
            'description',
            'amOpeningTime',
            'pmOpeningTime',
            'maxGuest'
        ],
        properties: [
            new OA\Property(
                property: 'name',
                type: 'string',
                example: 'Nom du restaurant'
            ),
            new OA\Property(
                property: 'description',
                type: 'string',
                example: 'Description du restaurant'
            ),
            new OA\Property(
                property: 'amOpeningTime',
                type: 'array',
                items: new OA\Items(
                    type: 'string'
                ),
                example: ['12:00', '14:00']
            ),
            new OA\Property(
                property: 'pmOpeningTime',
                type: 'array',
                items: new OA\Items(
                    type: 'string'
                ),
                example: ['19:00', '22:00']
            ),
            new OA\Property(
                property: 'maxGuest',
                type: 'integer',
                example: 60
            )
        ]
    )
)]

    #[OA\Response(
        response: 201,
        description: 'Restaurant créé avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                    example: 1
                ),
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Nom du restaurant'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    example: 'Description du restaurant'
                ),
                new OA\Property(
                    property: 'createdAt',
                    type: 'string',
                    format: 'date-time'
                )
            ]
        )
    )]

    public function new(Request $request): JsonResponse
    {
        $restaurant = $this->serializer->deserialize($request->getContent(), type: Restaurant::class, format: 'json');
        $restaurant->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($restaurant);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($restaurant, format:'json');
        $location = $this->urlGenerator->generate(
            name: 'app_api_restaurant_show',
            parameters: ['id' => $restaurant->getId()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse(
            $responseData,
            status: Response::HTTP_CREATED,
            headers: ["Location" => $location],
            json: true
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]

    #[OA\Get(
        path: '/api/restaurant/{id}',
        summary: 'Afficher un restaurant par ID'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du restaurant à afficher',
        schema: new OA\Schema(
            type: 'integer'
        )
    )]

    #[OA\Response(
        response: 200,
        description: 'Restaurant trouvé avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                    example: 1
                ),
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Nom du restaurant'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    example: 'Description du restaurant'
                ),
                new OA\Property(
                    property: 'createdAt',
                    type: 'string',
                    format: 'date-time'
                )
            ]
        )
    )]

    #[OA\Response(
        response: 404,
        description: 'Restaurant non trouvé'
    )]
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);
        if ($restaurant) {
            $responseData = $this->serializer->serialize($restaurant, 'json');

            return new JsonResponse(
                data: $responseData,
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
    #[OA\Put(
        path: '/api/restaurant/{id}',
        summary: 'Modifier un restaurant par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du restaurant à modifier',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Nouvelles données du restaurant',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Nouveau nom'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    example: 'Nouvelle description du restaurant'
                ),
                new OA\Property(
                    property: 'amOpeningTime',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['12:00', '14:00']
                ),
                new OA\Property(
                    property: 'pmOpeningTime',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['19:00', '22:00']
                ),
                new OA\Property(
                    property: 'maxGuest',
                    type: 'integer',
                    example: 80
                )
            ]
        )
    )]

    #[OA\Response(
        response: 204,
        description: 'Restaurant modifié avec succès'
    )]

    #[OA\Response(
        response: 404,
        description: 'Restaurant non trouvé'
    )]

    public function edit(int $id, Request $request): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        if ($restaurant) {
            $restaurant = $this->serializer->deserialize(
                $request->getContent(),
                type: Restaurant::class,
                format: 'json',
                context: [AbstractNormalizer::OBJECT_TO_POPULATE => $restaurant]
            );
            $restaurant->setUpdatedAt(new DateTimeImmutable());
            
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
    #[OA\Delete(
        path: '/api/restaurant/{id}',
        summary: 'Supprimer un restaurant'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du restaurant à supprimer',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\Response(
        response: 204,
        description: 'Restaurant supprimé avec succès'
    )]

    #[OA\Response(
        response: 404,
        description: 'Restaurant non trouvé'
    )]
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