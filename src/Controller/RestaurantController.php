<?php

namespace App\Controller;

use App\Entity\Restaurant;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/restaurant', name: 'app_api_restaurant_')]
final class RestaurantController extends AbstractController
{
    #[Route('/', name: 'new', methods: 'POST')]
    public function new(): Response
    {
       $restaurant = new Restaurant();
       $restaurant->setName( name: 'Quai Antique');
       $restaurant->setDescription( description: 'Cette qualité et ce goût par le chef Arnaud Michant');
       $restaurant->setCreatedAt(new DateTimeImmutable());

       // A stocker en base

       return $this->json(
        ['message' => "Restaurant resource created with {$restaurant->getId()} id"],
        Response::HTTP_CREATED,
       );
    }

    #[Route('/show', name: 'show', methods: 'GET')]
    public function show(): Response
    {
       return $this->json(['message' => 'Restaurant de ma BDD']);
    }
    
    #[Route('/', name: 'edit', methods: 'PUT')]
    public function edit(): Response
    {
        
    }
    #[Route('/', name: 'delete', methods: 'DELETE')]
    public function delete(): Response
    {
        
    }

}
