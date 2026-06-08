<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use DateTime;
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

#[Route('/api/category', name: 'app_api_category_')]
class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CategoryRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/', name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/category/',
        summary: 'Créer une catégorie'
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Données de la catégorie à créer',
        content: new OA\JsonContent(
            type: 'object',
            required: ['title'],
            properties: [
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'Entrées'
                )
            ]
        )
    )]

    #[OA\Response(
        response: 201,
        description: 'Catégorie créée avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                    example: 1
                ),
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'Entrées'
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
        $category = $this->serializer->deserialize($request->getContent(), type: Category::class, format: 'json');
        $category->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($category);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($category, format:'json');
        $location = $this->urlGenerator->generate(
            name: 'app_api_category_show',
            parameters: ['id' => $category->getId()],
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
        path: '/api/category/{id}',
        summary: 'Afficher une catégorie par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de la catégorie',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\Response(
        response: 200,
        description: 'Catégorie trouvée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                    example: 1
                ),
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'Entrées'
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
        description: 'Catégorie non trouvée'
    )]

    public function show(int $id): JsonResponse
    {
        $category = $this->repository->findOneBy(['id' => $id]);
        if ($category) {
            $responseData = $this->serializer->serialize($category, 'json');

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
        path: '/api/category/{id}',
        summary: 'Modifier une catégorie par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de la catégorie à modifier',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Nouvelles données de la catégorie',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'Plats'
                )
            ]
        )
    )]

    #[OA\Response(
        response: 204,
        description: 'Catégorie modifiée avec succès'
    )]

    #[OA\Response(
        response: 404,
        description: 'Catégorie non trouvée'
    )]
    
    public function edit(int $id, Request $request): JsonResponse
    {
        $category = $this->repository->findOneBy(['id' => $id]);

        if ($category) {
            $category = $this->serializer->deserialize(
                $request->getContent(),
                type: Category::class,
                format: 'json',
                context: [AbstractNormalizer::OBJECT_TO_POPULATE => $category]
            );
            $category->setUpdatedAt(new DateTime());
            
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
        path: '/api/category/{id}',
        summary: 'Supprimer une catégorie par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de la catégorie à supprimer',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\Response(
        response: 200,
        description: 'Catégorie supprimée avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Category with id n°1 deleted successfully'
                )
            ]
        )
    )]

    #[OA\Response(
        response: 404,
        description: 'Catégorie non trouvée'
    )]
    
    public function delete(int $id): JsonResponse
    {
        $category = $this->repository->findOneBy(['id' => $id]);

        if ($category) {
            $this->manager->remove($category);
            $this->manager->flush();

        return new JsonResponse(
            ['message' => "Category with id n°{$id} deleted successfully"],
            Response::HTTP_OK
        );

        }

        return new JsonResponse(
            data: null,
            status: Response::HTTP_NOT_FOUND
        );
    }
}