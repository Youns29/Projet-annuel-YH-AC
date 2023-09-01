<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $invoiceNumber = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $invoiceDate = null;

    #[ORM\Column(length: 50)]
    private ?string $totalAmount = null;

    #[ORM\Column(length: 255)]
    private ?string $totalWithoutTaxes = null;

    #[ORM\Column(length: 255)]
    private ?string $taxeAmount = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'invoicesAddress')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $invoiceAddress = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceNumber(): ?int
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(int $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $invoiceDate): static
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }
    
    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getTotalWithoutTaxes(): ?string
    {
        return $this->totalWithoutTaxes;
    }

    public function setTotalWithoutTaxes(string $totalWithoutTaxes): static
    {
        $this->totalWithoutTaxes = $totalWithoutTaxes;

        return $this;
    }

    public function getTaxeAmount(): ?string
    {
        return $this->taxeAmount;
    }

    public function setTaxeAmount(string $taxeAmount): static
    {
        $this->taxeAmount = $taxeAmount;

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

    public function getInvoiceAddress(): ?User
    {
        return $this->invoiceAddress;
    }

    public function setInvoiceAddress(?User $invoiceAddress): static
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
