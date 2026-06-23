<?php

namespace App\Entity;

use App\Repository\QcmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QcmRepository::class)]
class Qcm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $gainPts = null;

    #[ORM\OneToOne(targetEntity: Activite::class, inversedBy: 'qcm')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Activite $activite = null;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'qcm', cascade: ['persist', 'remove'])]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s (%d pts)', $this->activite ?? '?', $this->gainPts ?? 0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGainPts(): ?int
    {
        return $this->gainPts;
    }

    public function setGainPts(int $gainPts): static
    {
        $this->gainPts = $gainPts;

        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): static
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    // For easy admin, don't remove.
    public function getQuestionsView(): Collection
    {
        return $this->questions;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQcm($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getQcm() === $this) {
                $question->setQcm(null);
            }
        }

        return $this;
    }
}
