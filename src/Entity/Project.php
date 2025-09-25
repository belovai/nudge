<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'projects')]
class Project
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(['project:public'])]
    private Uuid $uuid;

    #[ORM\Column(length: 255)]
    #[Groups(['project:public'])]
    private string $name;

    /**
     * @var Collection<int, Version>
     */
    #[ORM\OneToMany(targetEntity: Version::class, mappedBy: 'project', orphanRemoval: true)]
    private Collection $versions;

    public function __construct(string $name)
    {
        $this->uuid = Uuid::v4();
        $this->name = $name;
        $this->versions = new ArrayCollection();
    }

    public function getUuid(): string
    {
        return $this->uuid->toRfc4122();
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

    /**
     * @return Collection<int, Version>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(Version $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setProject($this);
        }

        return $this;
    }

    public function removeVersion(Version $version): static
    {
        if ($this->versions->removeElement($version)) {
            // set the owning side to null (unless already changed)
            if ($version->getProject() === $this) {
                $version->setProject(null);
            }
        }

        return $this;
    }
}
