<?php
namespace App\Controller;

use App\Entity\Booking;
use App\Entity\User;
use App\Repository\BookingRepository;
use App\Repository\RestaurantRepository;
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

#[Route('/api/booking', name: 'app_api_booking_')]
class BookingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private BookingRepository $repository,
        private RestaurantRepository $restaurantRepository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/', name: 'new', methods: ['POST'])]
    #[OA\Post(
        path: '/api/booking/',
        summary: 'Créer une réservation'
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Données de la réservation',
        content: new OA\JsonContent(
            type: 'object',
            required: ['guestNumber', 'orderDate', 'orderHour', 'restaurantId'],
            properties: [
                new OA\Property(
                    property: 'guestNumber',
                    type: 'integer',
                    example: 4
                ),
                new OA\Property(
                    property: 'orderDate',
                    type: 'string',
                    example: '2026-06-20'
                ),
                new OA\Property(
                    property: 'orderHour',
                    type: 'string',
                    example: '2026-06-20 20:00:00'
                ),
                new OA\Property(
                    property: 'allergy',
                    type: 'string',
                    example: 'Pomme'
                ),
                new OA\Property(
                    property: 'restaurantId',
                    type: 'integer',
                    example: 1
                )
            ]
        )
    )]

    #[OA\Response(
        response: 201,
        description: 'Réservation créée avec succès'
    )]

    #[OA\Response(
        response: 401,
        description: 'Utilisateur non authentifié'
    )]

    #[OA\Response(
        response: 404,
        description: 'Restaurant introuvable'
    )]
    public function new(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(
                ['message' => 'User not authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $data = json_decode($request->getContent(), true);

        if (
            !isset(
                $data['guestNumber'],
                $data['orderDate'],
                $data['orderHour'],
                $data['restaurantId']
            )
        ) {
            return new JsonResponse(
                ['message' => 'Missing required fields'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $restaurant = $this->restaurantRepository->find($data['restaurantId']);

        if (!$restaurant) {
            return new JsonResponse(
                ['message' => 'Restaurant not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $booking = new Booking();

        $booking
            ->setGuestNumber($data['guestNumber'])
            ->setOrderDate(new \DateTime($data['orderDate']))
            ->setOrderHour(new \DateTime($data['orderHour']))
            ->setAllergy($data['allergy'] ?? null)
            ->setRestaurant($restaurant)
            ->setClient($user)
            ->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($booking);
        $this->manager->flush();

        $responseData = $this->serializer->serialize(
            $booking,
            'json',
            ['groups' => ['booking']]
        );

        $location = $this->urlGenerator->generate(
            'app_api_booking_show',
            ['id' => $booking->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            $responseData,
            Response::HTTP_CREATED,
            ['Location' => $location],
            true
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/booking/{id}',
        summary: 'Afficher une réservation par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de la réservation',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\Response(
        response: 200,
        description: 'Réservation trouvée'
    )]

    #[OA\Response(
        response: 404,
        description: 'Réservation introuvable'
    )]
    public function show(int $id): JsonResponse
    {
        $booking = $this->repository->find($id);

        if (!$booking) {
            return new JsonResponse(
                data: null,
                status: Response::HTTP_NOT_FOUND
            );
        }

        $responseData = $this->serializer->serialize(
            $booking,
            'json',
            ['groups' => ['booking']]
        );

        return new JsonResponse(
            data: $responseData,
            status: Response::HTTP_OK,
            json: true
        );
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/booking/{id}',
        summary: 'Modifier une réservation par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de la réservation à modifier',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Nouvelles données de réservation',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'guestNumber',
                    type: 'integer',
                    example: 6
                ),
                new OA\Property(
                    property: 'orderDate',
                    type: 'string',
                    example: '2026-06-25'
                ),
                new OA\Property(
                    property: 'orderHour',
                    type: 'string',
                    example: '2026-06-25 21:00:00'
                ),
                new OA\Property(
                    property: 'allergy',
                    type: 'string',
                    example: 'Fruits à coque'
                )
            ]
        )
    )]

    #[OA\Response(
        response: 204,
        description: 'Réservation modifiée avec succès'
    )]

    #[OA\Response(
        response: 404,
        description: 'Réservation introuvable'
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $booking = $this->repository->find($id);

        if (!$booking) {
            return new JsonResponse(
                data: null,
                status: Response::HTTP_NOT_FOUND
            );
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Booking::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $booking
            ]
        );

        $booking->setUpdatedAt(new DateTimeImmutable());

        $this->manager->flush();

        return new JsonResponse(
            data: null,
            status: Response::HTTP_NO_CONTENT
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/booking/{id}',
        summary: 'Supprimer une réservation par id'
    )]

    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de la réservation à supprimer',
        schema: new OA\Schema(
            type: 'integer',
            example: 1
        )
    )]

    #[OA\Response(
        response: 200,
        description: 'Réservation supprimée avec succès'
    )]

    #[OA\Response(
        response: 404,
        description: 'Réservation introuvable'
    )]
    public function delete(int $id): JsonResponse
    {
        $booking = $this->repository->find($id);

        if (!$booking) {
            return new JsonResponse(
                data: null,
                status: Response::HTTP_NOT_FOUND
            );
        }

        $this->manager->remove($booking);
        $this->manager->flush();

        return new JsonResponse(
            ['message' => "Booking with id n°{$id} deleted successfully"],
            Response::HTTP_OK
        );
    }
}