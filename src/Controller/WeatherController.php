<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GetWeatherService;
use Symfony\Contracts\Cache\CacheInterface;
final class WeatherController extends AbstractController
{
    #[Route('/weather', name: 'app_weather')]
    public function index(GetWeatherService $getWeatherService, CacheInterface $cache): Response
    {
        $weather_in_cities = $cache->get('weather_data', function ($item) use ($getWeatherService) {
            $item->expiresAfter(900);
            $cities = ['Saint Petersburg', 'Moscow', 'Volgograd', 'Archangelsk', 'Zvenigovo',
                'London', 'Khabarovsk', 'Magadan', 'Paris', 'Ekaterinburg',
            ];
            $data = [];
            foreach ($cities as $city) {
                $data[] = $getWeatherService->getWeather($city);
            }
            return $data;
        });
        return $this->render('weather/index.html.twig', [
            'weather_in_cities' => $weather_in_cities
        ]);
    }

    #[Route('/weather/download', name: 'weather_download')]
    public function generateFile(CacheInterface $cache): Response
    {
        $weather_in_cities = $cache->get('weather_data', fn () => []);

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
                $temp->city,
                $temp->temperature,
                $temp->feels,
                $temp->humidity,
                $temp->pressure,
                $temp->description,
                $temp->wind_speed,
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
}
