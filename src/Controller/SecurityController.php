<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
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