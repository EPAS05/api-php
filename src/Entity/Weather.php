<?php
namespace App\Entity;

class Weather {
    public string $city;
    public float $temperature;
    public float $feels;
    public int $humidity;
    public int $pressure;
    public string $description;
    public float $wind_speed;

    public function __construct($city, $temperature, $feels, $humidity, $pressure, $description, $wind_speed) {
        $this->city = $city;
        $this->temperature = $temperature;
        $this->feels = $feels;
        $this->humidity = $humidity;
        $this->pressure = $pressure;
        $this->description = $description;
        $this->wind_speed = $wind_speed;
    }
}
