<?php

namespace App\Entity;

use App\Repository\ActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActiviteRepository::class)]
class Activite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'activites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cours $cours = null;

    #[ORM\OneToOne(targetEntity: Contenu::class, mappedBy: 'activite', cascade: ['persist', 'remove'])]
    private ?Contenu $contenu = null;

    #[ORM\OneToOne(targetEntity: Qcm::class, mappedBy: 'activite', cascade: ['persist', 'remove'])]
    private ?Qcm $qcm = null;

    /**
     * @var Collection<int, ActiviteProgression>
     */
    #[ORM\OneToMany(targetEntity: ActiviteProgression::class, mappedBy: 'activite', orphanRemoval: true)]
    private Collection $activiteProgressions;

    public function __construct()
    {
        $this->activiteProgressions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s n°%d — %s', $this->type ?? '?', $this->ordre ?? 0, $this->cours ?? '?');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

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

    public function getContenu(): ?Contenu
    {
        return $this->contenu;
    }

    public function setContenu(?Contenu $contenu): static
    {
        if ($contenu === null && $this->contenu !== null) {
            $this->contenu->setActivite(null);
        }

        if ($contenu !== null) {
            $contenu->setActivite($this);
        }

        $this->contenu = $contenu;

        return $this;
    }

    public function getQcm(): ?Qcm
    {
        return $this->qcm;
    }

    public function setQcm(?Qcm $qcm): static
    {
        if ($qcm === null && $this->qcm !== null) {
            $this->qcm->setActivite(null);
        }

        if ($qcm !== null) {
            $qcm->setActivite($this);
        }

        $this->qcm = $qcm;

        return $this;
    }

    /**
     * @return Collection<int, ActiviteProgression>
     */
    // For easy admin, don't remove.
    public function getContenuLink(): ?Contenu
    {
        return $this->contenu;
    }

    // For easy admin, don't remove.
    public function getQcmLink(): ?Qcm
    {
        return $this->qcm;
    }

    // For easy admin, don't remove.
    public function getActiviteProgressionsView(): Collection
    {
        return $this->activiteProgressions;
    }

    public function getActiviteProgressions(): Collection
    {
        return $this->activiteProgressions;
    }

    public function addActiviteProgression(ActiviteProgression $activiteProgression): static
    {
        if (!$this->activiteProgressions->contains($activiteProgression)) {
            $this->activiteProgressions->add($activiteProgression);
            $activiteProgression->setActivite($this);
        }

        return $this;
    }

    public function removeActiviteProgression(ActiviteProgression $activiteProgression): static
    {
        if ($this->activiteProgressions->removeElement($activiteProgression)) {
            // set the owning side to null (unless already changed)
            if ($activiteProgression->getActivite() === $this) {
                $activiteProgression->setActivite(null);
            }
        }

        return $this;
    }
}
