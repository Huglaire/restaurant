<?php

namespace App\Controller;

use App\Entity\Food;
use App\Repository\FoodRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/food', name: 'app_api_food_')]
class FoodController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private FoodRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/', name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/food/',
        summary: 'Créer un plat'
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Données du plat à créer',
        content: new OA\JsonContent(
            type: 'object',
            required: ['title', 'price'],
            properties: [
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'Nom du plat'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    example: 'Description du plat'
                ),
                new OA\Property(
                    property: 'price',
                    type: 'integer',
                    example: 29
                )
            ]
        )
    )]

    #[OA\Response(
        response: 201,
        description: 'Plat créé avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'title', type: 'string', example: 'Filet de bœuf'),
                new OA\Property(property: 'description', type: 'string', example: 'Filet de bœuf sauce morilles'),
                new OA\Property(property: 'price', type: 'integer', example: 29),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
            ]
        )
    )]
    public function new(Request $request): JsonResponse
    {
        $food = $this->serializer->deserialize($request->getContent(), type: Food::class, format: 'json');
        $food->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($food);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($food, format:'json');
        $location = $this->urlGenerator->generate(
            name: 'app_api_food_show',
            parameters: ['id' => $food->getId()],
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
        path: '/api/food/{id}',
        summary: 'Afficher un plat par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du plat',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\Response(
        response: 200,
        description: 'Plat trouvé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'title', type: 'string', example: 'Filet de bœuf'),
                new OA\Property(property: 'description', type: 'string', example: 'Filet de bœuf sauce morilles'),
                new OA\Property(property: 'price', type: 'integer', example: 29),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
            ]
        )
    )]

    #[OA\Response(
        response: 404,
        description: 'Plat non trouvé'
    )]

    public function show(int $id): JsonResponse
    {
        $food = $this->repository->findOneBy(['id' => $id]);
        if ($food) {
            $responseData = $this->serializer->serialize($food, 'json');

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
        path: '/api/food/{id}',
        summary: 'Modifier un plat par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du plat à modifier',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Nouvelles données du plat',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'Nouveau nom du plat'
                ),
                new OA\Property(
                    property: 'description',
                    type: 'string',
                    example: 'Nouvelle description'
                ),
                new OA\Property(
                    property: 'price',
                    type: 'integer',
                    example: 32
                )
            ]
        )
    )]

    #[OA\Response(
        response: 204,
        description: 'Plat modifié avec succès'
    )]

    #[OA\Response(
        response: 404,
        description: 'Plat non trouvé'
    )]

    public function edit(int $id, Request $request): JsonResponse
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if ($food) {
            $food = $this->serializer->deserialize(
                $request->getContent(),
                type: Food::class,
                format: 'json',
                context: [AbstractNormalizer::OBJECT_TO_POPULATE => $food]
            );
            $food->setUpdatedAt(new DateTime());
            
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
        path: '/api/food/{id}',
        summary: 'Supprimer un plat avec son id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du plat à supprimer',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\Response(
        response: 204,
        description: 'Plat supprimé avec succès'
    )]

    #[OA\Response(
        response: 404,
        description: 'Plat non trouvé'
    )]

    public function delete(int $id): JsonResponse
    {
        $food = $this->repository->findOneBy(['id' => $id]);

        if ($food) {
            $this->manager->remove($food);
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