<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'app_api_')]
final class SecurityController extends AbstractController
{
    public function __construct (private EntityManagerInterface $manager, private SerializerInterface $serializer, private UserRepository $repository)
    {
    }
    #[Route('/registration', name: 'registration', methods: ['POST'])]

    #[OA\Post(
        path: '/api/registration',
        summary: 'Inscription d\'un nouvel utilisateur'
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Données de l\'utilisateur à inscrire',
        content: new OA\JsonContent(
            type: 'object',
            required: ['firstName', 'lastName', 'guestNumber', 'email', 'password'],
            properties: [
                new OA\Property(
                    property: 'firstName',
                    type: 'string',
                    example: 'Jean'
                ),
                new OA\Property(
                    property: 'lastName',
                    type: 'string',
                    example: 'Dupont'
                ),
                new OA\Property(
                    property: 'guestNumber',
                    type: 'integer',
                    example: 4
                ),
                new OA\Property(
                    property: 'allergy',
                    type: 'string',
                    example: 'Arachides'
                ),
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    example: 'adressemail@email.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    example: 'MotDePasse123'
                )
            ]
        )
    )]

    #[OA\Response(
        response: 201,
        description: 'Utilisateur inscrit avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'user',
                    type: 'string',
                    example: 'Nom d\'utilisateur'
                ),
                new OA\Property(
                    property: 'apiToken',
                    type: 'string',
                    example: '31a023e212f116124a36af14ea0c1c3806eb9378'
                ),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        type: 'string',
                        example: 'ROLE_USER'
                    )
                )
            ]
        )
    )]

    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), type: User::class, format: 'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            ['user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'role' => $user->getRoles()],
            status:Response::HTTP_CREATED);
    }


    #[Route('/login', name: 'login', methods:['POST'])]
    #[OA\Post(
        path: '/api/login',
        summary: 'Connecter un utilisateur'
    )]

    #[OA\RequestBody(
        required: true,
        description: 'Données de l\'utilisateur à inscrire',
        content: new OA\JsonContent(
            type: 'object',
            required: ['firstName', 'lastName', 'guestNumber', 'email', 'password'],
            properties: [
                new OA\Property(
                    property: 'username',
                    type: 'string',
                    example: 'Jean'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    example: 'MotDePasse123'
                )
            ]
        )
    )]

    #[OA\Response(
        response: 200,
        description: 'Connexion réussie',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'user',
                    type: 'string',
                    example: 'Nom d\'utilisateur'
                ),
                new OA\Property(
                    property: 'apiToken',
                    type: 'string',
                    example: '31a023e212f116124a36af14ea0c1c3806eb9378'
                ),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        type: 'string',
                        example: 'ROLE_USER'
                    )
                )
            ]
        )
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {

        if (null === $user) {
            return new JsonResponse(
                ['message' => 'missing credentials'],
                status: Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(
            ['user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'role' => $user->getRoles()]
        );
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Sérialise l'utilisateur sans groupe spécifique, tous les champs accessibles seront inclus.
        $data = $this->serializer->serialize($user, 'json', ['groups' => 'profile']);

        return new JsonResponse($data, json: true);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(
        int $id,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $user = $this->repository->findOneBy(['id' => $id]);

        if (!$user) {
            return new JsonResponse(
                data: null,
                status: Response::HTTP_NOT_FOUND
            );
        }

        // Mise à jour des champs de l'utilisateur existant
        $updatedUser = $this->serializer->deserialize(
            $request->getContent(),
            type: User::class,
            format: 'json',
            context: [
                AbstractNormalizer::OBJECT_TO_POPULATE => $user
            ]
        );

        // Récupération du JSON envoyé
        $data = json_decode($request->getContent(), true);

        // Si un mot de passe est fourni, on le hache
        if (isset($data['password']) && !empty($data['password'])) {
            $updatedUser->setPassword(
                $passwordHasher->hashPassword(
                    $updatedUser,
                    $data['password']
                )
            );
        }

        $updatedUser->setUpdatedAt(new DateTimeImmutable());

        $this->manager->flush();

        return new JsonResponse(
            data: null,
            status: Response::HTTP_NO_CONTENT
        );
    }
}