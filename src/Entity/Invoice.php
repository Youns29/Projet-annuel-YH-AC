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

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: "string", length: 255, nullable: "designation")]
    private ?string $designation = "Abonnement";

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $invoiceDate;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: false)]
    private string $totalAmount;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: false)]
    private string $totalWithoutTaxes;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: false)]
    private string $taxeAmount;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(name: 'cabinet_id', referencedColumnName: 'id', nullable: true)]
    private ?Cabinet $cabinet = null;


    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $created_at = null;


    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }
    
    public function setInvoiceNumber(string $invoiceNumber): static
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
    
    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

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

    public function getCabinet(): ?Cabinet
    {
        return $this->cabinet;
    }

    public function setCabinet(?Cabinet $cabinet): self
    {
        $this->cabinet = $cabinet;

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
