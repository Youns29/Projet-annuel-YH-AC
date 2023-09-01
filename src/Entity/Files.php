<?php

namespace App\Entity;

use App\Repository\FilesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilesRepository::class)]
class Files
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable:true)]
    private ?float $size = null;

    #[ORM\Column(length: 50, nullable:true)]
    private ?string $format = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'],  nullable:true)]
    private ?\DateTimeImmutable $uploadDate = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE,  nullable:true)]
    private ?\DateTimeInterface $lastModificationDate = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    public function __construct()
    {
        $this->uploadDate = new \DateTimeImmutable();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(float $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getUploadDate(): ?\DateTimeImmutable
    {
        return $this->uploadDate;
    }

    public function setUploadDate(\DateTimeImmutable $uploadDate): static
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLastModificationDate(): ?\DateTimeInterface
    {
        return $this->lastModificationDate;
    }

    public function setLastModificationDate(\DateTimeInterface $lastModificationDate): static
    {
        $this->lastModificationDate = $lastModificationDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
