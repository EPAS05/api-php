<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GetWeatherService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Weather;

final class WeatherController extends AbstractController
{
    #[Route('/weather', name: 'weather')]
    public function index(GetWeatherService $getWeatherService, EntityManagerInterface $en): Response
{
    $cities = ['Saint Petersburg', 'Moscow', 'Volgograd', 'Archangelsk', 'Zvenigovo',
                'London', 'Khabarovsk', 'Magadan', 'Paris', 'Ekaterinburg'
            ];
    $weather_in_cities = [];
    $now = new \DateTimeImmutable();
    $tm = $now->modify('-15 minutes');

    foreach ($cities as $city) {
        $weather = $en->getRepository(Weather::class)->findOneBy(['city' => $city]);

        if (!$weather) {
            $data = $getWeatherService->getWeather($city);

            $weather = new Weather(
                (string)$data->getCity(),
                (float)$data->getTemperature(),
                (float)$data->getFeels(),
                (int)$data->getHumidity(),
                (int)$data->getPressure(),
                (string)$data->getDescription(),
                (float)$data->getWindSpeed()
            );
            $weather->setUpdatedAt($now);

            $en->persist($weather);
        } else {
            $updatedAt = $weather->getUpdatedAt();
            if (!$updatedAt || $updatedAt <= $tm) {
                $data = $getWeatherService->getWeather($city);

                $weather->setTemperature((float)$data->getTemperature())
                    ->setFeels((float)$data->getFeels())
                    ->setHumidity((int)$data->getHumidity())
                    ->setPressure((int)$data->getPressure())
                    ->setDescription((string)$data->getDescription())
                    ->setWindSpeed((float)$data->getWindSpeed())
                    ->setUpdatedAt($now);
            }
        }

        $weather_in_cities[] = $weather;
    }

    $en->flush();

    return $this->render('weather/index.html.twig', [
        'weather_in_cities' => $weather_in_cities
    ]);
}


    #[Route('/weather/download', name: 'weather_download')]
    public function generateFile(EntityManagerInterface $en): Response
    {
        $weather_in_cities = $en->getRepository(Weather::class)->findAll();

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, [
            'Город',
            'Температура',
            'Ощущается',
            'Влажность',
            'Давление',
            'Описание',
            'Ветер'
        ], ',', '"', "\\");
        foreach ($weather_in_cities as $temp) {
            fputcsv($csv, [
                $temp->getCity(),
                $temp->getTemperature(),
                $temp->getFeels(),
                $temp->getHumidity(),
                $temp->getPressure(),
                $temp->getDescription(),
                $temp->getWindSpeed(),
            ], ',', '"', "\\");
        }
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);
        return new Response($csvContent,200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="weather' . date('Y-m-d') . '.csv"',
        ]);
    }
    #[Route('/weather/refresh', name: 'weather_refresh')]
    public function refresh(): Response
    {
        return $this->redirectToRoute('weather');
    }
}
