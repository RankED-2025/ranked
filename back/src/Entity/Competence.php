<?php

namespace App\Entity;

use App\Repository\CompetenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetenceRepository::class)]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $niveau = null;

    #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'competences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cours $cours = null;

    /**
     * @var Collection<int, EleveCompetence>
     */
    #[ORM\OneToMany(targetEntity: EleveCompetence::class, mappedBy: 'competence')]
    private Collection $eleveCompetences;

    public function __construct()
    {
        $this->eleveCompetences = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s — %s', $this->nom ?? '?', $this->cours ?? '?');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

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

    /**
     * @return Collection<int, EleveCompetence>
     */
    // For easy admin, don't remove.
    public function getEleveCompetencesView(): Collection
    {
        return $this->eleveCompetences;
    }

    public function getEleveCompetences(): Collection
    {
        return $this->eleveCompetences;
    }

    public function addEleveCompetence(EleveCompetence $eleveCompetence): static
    {
        if (!$this->eleveCompetences->contains($eleveCompetence)) {
            $this->eleveCompetences->add($eleveCompetence);
            $eleveCompetence->setCompetence($this);
        }

        return $this;
    }

    public function removeEleveCompetence(EleveCompetence $eleveCompetence): static
    {
        if ($this->eleveCompetences->removeElement($eleveCompetence)) {
            if ($eleveCompetence->getCompetence() === $this) {
                $eleveCompetence->setCompetence(null);
            }
        }

        return $this;
    }
}
