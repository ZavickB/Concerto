<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatusRepository::class)
 */
class Status
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Idea::class, mappedBy="status")
     */
    private $idea;

    public function __construct()
    {
        $this->idea = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Idea>
     */
    public function getIdea(): Collection
    {
        return $this->idea;
    }

    public function addIdea(Idea $idea): self
    {
        if (!$this->idea->contains($idea)) {
            $this->idea[] = $idea;
            $idea->setStatus($this);
        }

        return $this;
    }

    public function removeIdea(Idea $idea): self
    {
        if ($this->idea->removeElement($idea)) {
            // set the owning side to null (unless already changed)
            if ($idea->getStatus() === $this) {
                $idea->setStatus(null);
            }
        }

        return $this;
    }
}
