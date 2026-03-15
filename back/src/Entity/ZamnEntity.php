<?php

namespace App\Entity;

use App\Repository\ZamnEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZamnEntityRepository::class)]
class ZamnEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, TestEntity>
     */
    #[ORM\OneToMany(targetEntity: TestEntity::class, mappedBy: 'zamnEntity')]
    private Collection $entries;

    #[ORM\Column(length: 128)]
    private ?string $name = null;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, TestEntity>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(TestEntity $entry): static
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
            $entry->setZamnEntity($this);
        }

        return $this;
    }

    public function removeEntry(TestEntity $entry): static
    {
        if ($this->entries->removeElement($entry)) {
            // set the owning side to null (unless already changed)
            if ($entry->getZamnEntity() === $this) {
                $entry->setZamnEntity(null);
            }
        }

        return $this;
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
}
