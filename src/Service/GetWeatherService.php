<?php
namespace App\Service;

use App\Entity\Weather;

class GetWeatherService {
    private $apiKey;
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }
    public function getWeather(string $cityName): Weather {
        $queryParameters = ['q'=> $cityName,'appid' => $this->apiKey,'units'=>'metric','lang'=>'ru'];
        $apiUrl = "http://api.openweathermap.org/data/2.5/weather?" . http_build_query($queryParameters);
        $apiResponse = json_decode(file_get_contents($apiUrl), true);
        return new Weather(
            $apiResponse['name'],
            $apiResponse['main']['temp'],
            $apiResponse['main']['feels_like'],
            $apiResponse['main']['humidity'],
            $apiResponse['main']['pressure'],
            $apiResponse['weather'][0]['description'],
            $apiResponse['wind']['speed']
        );
    }
}