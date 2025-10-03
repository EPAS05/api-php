<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GetWeatherService;
use App\Service\WeatherCSVService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Weather;
use App\Entity\UserCity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;


final class WeatherController extends AbstractController
{
    private const WEATHER_CACHE_DURATION_MINUTES = 15;
    private const DEFAULT_CITIES = [
        'Saint Petersburg', 'Moscow', 'Volgograd', 'Zvenigovo',
        'Khabarovsk', 'Magadan', 'Ekaterinburg'
    ];

    public function __construct(
        private GetWeatherService $weatherService,
        private WeatherCSVService $csvGenerator,
        private EntityManagerInterface $entityManager
    ) {}

    private function getCitiesForUser(): array
    {
        $user = $this->getUser();
        $userCities = $this->entityManager->getRepository(UserCity::class)->findBy(['user' => $user]);
        $userCityNames = array_map(fn(UserCity $userCity) => $userCity->getCityName(), $userCities);
        return array_merge(self::DEFAULT_CITIES, $userCityNames);
    }
    
    private function getWeatherEntity(string $city): Weather
    {
        return $this->entityManager->getRepository(Weather::class)->findOneBy(['city' => $city]) ?? $this->weatherService->getWeather($city);
    }

    private function shouldUpdateWeatherData(Weather $weather, \DateTimeImmutable $cacheExpirationTime): bool
    {
        $lastUpdate = $weather->getUpdatedAt();
        return !$lastUpdate || $lastUpdate <= $cacheExpirationTime;
    }

    private function updateWeatherData(Weather $weather, string $city, \DateTimeImmutable $currentTime): void {
        $weatherData = $this->weatherService->getWeather($city);

        $weather
            ->setCity($weatherData->getCity())
            ->setTemperature($weatherData->getTemperature())
            ->setFeels($weatherData->getFeels())
            ->setHumidity($weatherData->getHumidity())
            ->setPressure($weatherData->getPressure())
            ->setDescription($weatherData->getDescription())
            ->setWindSpeed($weatherData->getWindSpeed())
            ->setUpdatedAt($currentTime);

        $this->entityManager->persist($weather);
    }

    private function getWeatherDataForCities(array $cities): array
    {
        $weatherInCities = [];
        $currentTime = new \DateTimeImmutable();
        $cacheExpirationTime = $currentTime->modify("-" . self::WEATHER_CACHE_DURATION_MINUTES . " minutes");

        foreach ($cities as $city) {
            $weatherEntity = $this->getWeatherEntity($city);
            
            if ($this->shouldUpdateWeatherData($weatherEntity, $cacheExpirationTime)) {
                $this->updateWeatherData($weatherEntity, $city, $currentTime);
            }
            $weatherInCities[] = $weatherEntity;
        }

        $this->entityManager->flush();

        return $weatherInCities;
    }

    #[Route('/weather', name: 'weather')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $cities = $this->getCitiesForUser();
        $weather= $this->getWeatherDataForCities($cities);
        
        return $this->render('weather/index.html.twig', [
            'weather_data' => $weather
        ]);
    }

    #[Route('/weather/download', name: 'weather_download')]
    public function downloadWeatherCSV(): Response
    {
        $cities = $this->getCitiesForUser();
        $weatherData = $this->getWeatherDataForCities($cities);
        return $this->csvGenerator->generateCsvResponse($weatherData);
    }
}