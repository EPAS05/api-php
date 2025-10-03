<?php

namespace App\Service;

use App\Entity\Weather;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class WeatherCSVService
{
    private const CSV_HEADERS = [
        'Город',
        'Температура', 
        'Ощущается',
        'Влажность',
        'Давление',
        'Описание',
        'Ветер'
    ];

    public function __construct(private EntityManagerInterface $entityManager) {}

    private function extractWeather(Weather $weather): array
    {
        return [
            $weather->getCity(),
            $weather->getTemperature(),
            $weather->getFeels(),
            $weather->getHumidity(),
            $weather->getPressure(),
            $weather->getDescription(),
            $weather->getWindSpeed(),
        ];
    }

    private function getResponseHeaders(): array
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="weather' . date('Y-m-d') . '.csv"',
        ];
    }

    private function generateCsvContent(array $weatherData): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, self::CSV_HEADERS);
        foreach ($weatherData as $weather) {
            fputcsv($handle, $this->extractWeather($weather));
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    public function generateCsvResponse(array $weatherData): Response
    {
        $csvContent = $this->generateCsvContent($weatherData);

        return new Response(
            $csvContent,
            Response::HTTP_OK,
            $this->getResponseHeaders()
        );
    }
}