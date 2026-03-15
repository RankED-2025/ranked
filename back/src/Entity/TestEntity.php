<?php

namespace App\Entity;

use App\Repository\TestEntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestEntityRepository::class)]
class TestEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $u_null = null;

    #[ORM\ManyToOne(inversedBy: 'entries')]
    private ?ZamnEntity $zamnEntity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUNull(): ?string
    {
        return $this->u_null;
    }

    public function setUNull(?string $u_null): static
    {
        $this->u_null = $u_null;

        return $this;
    }

    public function getZamnEntity(): ?ZamnEntity
    {
        return $this->zamnEntity;
    }

    public function setZamnEntity(?ZamnEntity $zamnEntity): static
    {
        $this->zamnEntity = $zamnEntity;

        return $this;
    }
}
