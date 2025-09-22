<?php
namespace App\Service;

use App\Entity\Weather;

class GetWeatherService {
    private $apiKey;
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }
    public function getWeather(string $city): Weather {
        $params = ['q'=> $city,'appid' => $this->apiKey,'units'=>'metric','lang'=>'ru'];
        $url = "http://api.openweathermap.org/data/2.5/weather?" . http_build_query($params);
        $json = json_decode(file_get_contents($url), true);
        return new Weather(
            $json['name'],
            $json['main']['temp'],
            $json['main']['feels_like'],
            $json['main']['humidity'],
            $json['main']['pressure'],
            $json['weather'][0]['description'],
            $json['wind']['speed']
        );
    }
}
