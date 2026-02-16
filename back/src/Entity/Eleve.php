<?php

namespace App\Entity;

use App\Repository\EleveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EleveRepository::class)]
class Eleve extends User
{
    #[ORM\ManyToOne(targetEntity: Classe::class, inversedBy: 'eleves')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Classe $classe = null;

    /**
     * @var Collection<int, Progression>
     */
    #[ORM\OneToMany(targetEntity: Progression::class, mappedBy: 'eleve')]
    private Collection $progressions;

    /**
     * @var Collection<int, EleveCompetence>
     */
    #[ORM\OneToMany(targetEntity: EleveCompetence::class, mappedBy: 'eleve')]
    private Collection $eleveCompetences;

    public function __construct()
    {
        $this->progressions = new ArrayCollection();
        $this->eleveCompetences = new ArrayCollection();
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

    /**
     * @return Collection<int, Progression>
     */
    public function getProgressions(): Collection
    {
        return $this->progressions;
    }

    public function addProgression(Progression $progression): static
    {
        if (!$this->progressions->contains($progression)) {
            $this->progressions->add($progression);
            $progression->setEleve($this);
        }

        return $this;
    }

    public function removeProgression(Progression $progression): static
    {
        if ($this->progressions->removeElement($progression)) {
            if ($progression->getEleve() === $this) {
                $progression->setEleve(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EleveCompetence>
     */
    public function getEleveCompetences(): Collection
    {
        return $this->eleveCompetences;
    }

    public function addEleveCompetence(EleveCompetence $eleveCompetence): static
    {
        if (!$this->eleveCompetences->contains($eleveCompetence)) {
            $this->eleveCompetences->add($eleveCompetence);
            $eleveCompetence->setEleve($this);
        }

        return $this;
    }

    public function removeEleveCompetence(EleveCompetence $eleveCompetence): static
    {
        if ($this->eleveCompetences->removeElement($eleveCompetence)) {
            if ($eleveCompetence->getEleve() === $this) {
                $eleveCompetence->setEleve(null);
            }
        }

        return $this;
    }
}
