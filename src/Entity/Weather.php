<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "weather")]
class Weather
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $city;

    #[ORM\Column(type: 'float')]
    private float $temperature;

    #[ORM\Column(type: 'float')]
    private float $feels;

    #[ORM\Column(type: 'integer')]
    private int $humidity;

    #[ORM\Column(type: 'integer')]
    private int $pressure;

    #[ORM\Column(length: 255)]
    private string $description;

    #[ORM\Column(type: 'float')]
    private float $wind_speed;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct(
        string $city,
        float $temperature,
        float $feels,
        int $humidity,
        int $pressure,
        string $description,
        float $wind_speed
    ) {
        $this->city = $city;
        $this->temperature = $temperature;
        $this->feels = $feels;
        $this->humidity = $humidity;
        $this->pressure = $pressure;
        $this->description = $description;
        $this->wind_speed = $wind_speed;
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getCity(): string { return $this->city; }
    public function getTemperature(): float { return $this->temperature; }
    public function getFeels(): float { return $this->feels; }
    public function getHumidity(): int { return $this->humidity; }
    public function getPressure(): int { return $this->pressure; }
    public function getDescription(): string { return $this->description; }
    public function getWindSpeed(): float { return $this->wind_speed; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updated_at; }

    public function setCity(string $city): self { $this->city = $city; return $this; }
    public function setTemperature(float $temperature): self { $this->temperature = $temperature; return $this; }
    public function setFeels(float $feels): self { $this->feels = $feels; return $this; }
    public function setHumidity(int $humidity): self { $this->humidity = $humidity; return $this; }
    public function setPressure(int $pressure): self { $this->pressure = $pressure; return $this; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    public function setWindSpeed(float $wind_speed): self { $this->wind_speed = $wind_speed; return $this; }
    public function setUpdatedAt(\DateTimeImmutable $updated_at): static { $this->updated_at = $updated_at; return $this; }
}
