<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'app_api_')]
final class SecurityController extends AbstractController
{
    public function __construct (private EntityManagerInterface $manager, private SerializerInterface $serializer)
    {
    }
    #[Route('/registration', name: 'registration', methods:['POST'])]
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
}