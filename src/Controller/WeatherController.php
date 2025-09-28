<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GetWeatherService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Weather;
use App\Entity\UserCity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;


final class WeatherController extends AbstractController
{
    private const WEATHER_CACHE_DURATION = '-15 minutes';
    private const DEFAULT_CITIES = [
        'Saint Petersburg', 'Moscow', 'Volgograd', 'Zvenigovo',
        'Khabarovsk', 'Magadan', 'Ekaterinburg'
    ];


    #[Route('/weather', name: 'weather')]
    public function index(GetWeatherService $weatherService, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    $currentUser = $this->getUser();
    $userCities = $entityManager->getRepository(UserCity::class)->findBy(['user' => $currentUser]);
    $userCityNames = array_map(fn($userCity) => $userCity->getCityName(), $userCities);
    $citiesToDisplay = array_merge(self::DEFAULT_CITIES, $userCityNames);

    $weatherInCities = [];
    $currentTime = new \DateTimeImmutable();
    $chacheExpiryTime = $currentTime->modify(self::WEATHER_CACHE_DURATION);

    foreach ($citiesToDisplay as $cityName) {
        $chachedWeather = $entityManager->getRepository(Weather::class)->findOneBy(['city' => $cityName]);

        if (!$chachedWeather) {
            $freshWeather = $weatherService->getWeather($cityName);

            $chachedWeather = new Weather(
                $freshWeather->getCity(),
                $freshWeather->getTemperature(),
                $freshWeather->getFeels(),
                $freshWeather->getHumidity(),
                $freshWeather->getPressure(),
                $freshWeather->getDescription(),
                $freshWeather->getWindSpeed()
            );
            $chachedWeather->setUpdatedAt($currentTime);
            $entityManager->persist($chachedWeather);
        } else {
            $lastUpdated = $chachedWeather->getUpdatedAt();
            if (!$lastUpdated || $lastUpdated <= $chacheExpiryTime) {
                $freshWeather = $weatherService->getWeather($cityName);

                $chachedWeather->setTemperature($freshWeather->getTemperature())
                    ->setFeels($freshWeather->getFeels())
                    ->setHumidity($freshWeather->getHumidity())
                    ->setPressure($freshWeather->getPressure())
                    ->setDescription($freshWeather->getDescription())
                    ->setWindSpeed($freshWeather->getWindSpeed())
                    ->setUpdatedAt($currentTime);
            }
        }

        $weatherInCities[] = $chachedWeather;
    }

    try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
        
        }
    return $this->render('weather/index.html.twig', [
        'weather_data' => $weatherInCities
    ]);
}


    #[Route('/weather/download', name: 'weather_download')]
    public function generateFile(EntityManagerInterface $entityManager): Response
    {
        $allWeatherRecords = $entityManager->getRepository(Weather::class)->findAll();

        $csvFile = fopen('php://temp', 'r+');
        fputcsv($csvFile, [
            'Город',
            'Температура',
            'Ощущается',
            'Влажность',
            'Давление',
            'Описание',
            'Ветер'
        ], ',', '"', "\\");
        foreach ($allWeatherRecords as $weatherRecord) {
            fputcsv($csvFile, [
                $weatherRecord->getCity(),
                $weatherRecord->getTemperature(),
                $weatherRecord->getFeels(),
                $weatherRecord->getHumidity(),
                $weatherRecord->getPressure(),
                $weatherRecord->getDescription(),
                $weatherRecord->getWindSpeed(),
            ], ',', '"', "\\");
        }
        rewind($csvFile);
        $csvContent = stream_get_contents($csvFile);
        fclose($csvFile);
        return new Response($csvContent,200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="weather' . date('Y-m-d') . '.csv"',
        ]);
    }
}