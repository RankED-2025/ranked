<?php

namespace App\Entity;

use App\Repository\ProgressionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProgressionRepository::class)]
class Progression
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Eleve::class, inversedBy: 'progressions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Eleve $eleve = null;

    #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'progressions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cours $cours = null;

    #[ORM\ManyToOne(targetEntity: Classe::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Classe $classe = null;

    #[ORM\ManyToOne(targetEntity: Badge::class, inversedBy: 'progressions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Badge $badge = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $percentage = 0;

    public function __toString(): string
    {
        return sprintf('%s — %d%%', $this->cours ?? '?', $this->percentage);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEleve(): ?Eleve
    {
        return $this->eleve;
    }

    public function setEleve(?Eleve $eleve): static
    {
        $this->eleve = $eleve;

        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;

        return $this;
    }

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): static
    {
        $this->classe = $classe;

        return $this;
    }

    public function getBadge(): ?Badge
    {
        return $this->badge;
    }

    public function setBadge(?Badge $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    public function setPercentage(int $percentage): static
    {
        if ($percentage < 0) {
            $percentage = 0;
        }

        if ($percentage > 100) {
            $percentage = 100;
        }

        $this->percentage = $percentage;

        return $this;
    }
}
