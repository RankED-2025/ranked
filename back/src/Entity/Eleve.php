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

    /**
     * @var Collection<int, ActiviteProgression>
     */
    #[ORM\OneToMany(targetEntity: ActiviteProgression::class, mappedBy: 'eleve', orphanRemoval: true)]
    private Collection $activiteProgressions;

    public function __construct()
    {
        $this->progressions = new ArrayCollection();
        $this->eleveCompetences = new ArrayCollection();
        $this->activiteProgressions = new ArrayCollection();
    }

    // For easy admin, don't remove.
    public function getProgressionsView(): Collection
    {
        return $this->progressions;
    }

    // For easy admin, don't remove.
    public function getEleveCompetencesView(): Collection
    {
        return $this->eleveCompetences;
    }

    // For easy admin, don't remove.
    public function getActiviteProgressionsView(): Collection
    {
        return $this->activiteProgressions;
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

    /**
     * @return Collection<int, ActiviteProgression>
     */
    public function getActiviteProgressions(): Collection
    {
        return $this->activiteProgressions;
    }

    public function addActiviteProgression(ActiviteProgression $activiteProgression): static
    {
        if (!$this->activiteProgressions->contains($activiteProgression)) {
            $this->activiteProgressions->add($activiteProgression);
            $activiteProgression->setEleve($this);
        }

        return $this;
    }

    public function removeActiviteProgression(ActiviteProgression $activiteProgression): static
    {
        if ($this->activiteProgressions->removeElement($activiteProgression)) {
            // set the owning side to null (unless already changed)
            if ($activiteProgression->getEleve() === $this) {
                $activiteProgression->setEleve(null);
            }
        }

        return $this;
    }
}
