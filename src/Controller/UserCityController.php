<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\UserCity;

#[Route('/api/user/cities', name: 'api_user_cities_')]
class UserCityController extends AbstractController
{
    #[Route('', name: 'get', methods: ['GET'])]
    public function getCities(EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $cities = $entityManager->getRepository(UserCity::class)->findBy(['user' => $user]);
        $data = [];
        foreach ($cities as $city) {
            $data[] = [
                'id' => $city->getId(),
                'cityName' => $city->getCityName(),
                'createdAt' => $city->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }
        
        return $this->json($data);
    }
    #[Route('', name: 'add', methods: ['POST'])]
    public function addCity(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $data = json_decode($request->getContent(), true);
        $cityName = $data['cityName'] ?? null;

        if (!$cityName) {
            return $this->json(['error' => 'no city name'], 400);
        }

        $user = $this->getUser();
        $city = new UserCity();
        $city->setUser($user);
        $city->setCityName($cityName);
        $entityManager->persist($city);
        $entityManager->flush();
        return $this->json([
            'id' => $city->getId(),
            'cityName' => $city->getCityName(),
            'createdAt' => $city->getCreatedAt()->format('Y-m-d H:i:s')
        ], 201);
    }
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteCity(UserCity $city, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($city->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $entityManager->remove($city);
        $entityManager->flush();
        
        return $this->json(['message' => 'City deleted successfully']);
    }
}
