<?php

namespace App\Entity;

use App\Repository\UserCityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCityRepository::class)]
class UserCity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userCities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $cityName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User {
        return $this->user;
    }
    public function getCityName(): ?string {
        return $this->cityName;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->createdAt;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function setCityName(string $cityName): static
    {
        $this->cityName = $cityName;
        return $this;
    }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
