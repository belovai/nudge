<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BuildRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BuildRepository::class)]
#[ORM\Table(name: 'builds')]
class Build
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $tag;

    #[ORM\Column(type: 'smallint')]
    private int $revision;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $context;

    #[ORM\ManyToOne(inversedBy: 'builds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Version $version = null;

    #[Groups(['build:public'])]
    public string $displayVersion {
        get {
            return $this->version->getVersion().'-'.$this->tag.'.'.$this->revision;
        }
    }

    public function __construct(string $tag, int $revision, ?array $context = null)
    {
        $this->tag = $tag;
        $this->revision = $revision;
        $this->context = $context;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): ?Version
    {
        return $this->version;
    }

    public function setVersion(?Version $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): static
    {
        $this->revision = $revision;

        return $this;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): static
    {
        $this->context = $context;

        return $this;
    }
}
