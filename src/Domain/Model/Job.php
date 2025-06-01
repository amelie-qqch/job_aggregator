<?php

namespace App\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Job
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(nullable: false)]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private string $title;

    #[ORM\Column(nullable: false)]
    private string $companyName;

    #[ORM\Column(nullable: true)]
    private string $location;

    #[ORM\Column(
        type: 'text',
        nullable: true
    )]
    private string $description;

    #[ORM\Column(nullable: false)]
    private string $applyUrl;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $datePosted;

    public function __construct(
        string $title,
        string | null $companyName,
        string $location,
        string $description,
        string $applyUrl,
        \DateTime $datePosted
    ) {
        $this->title        = $title;
        $this->companyName  = $companyName ?? "unknown";
        $this->location     = $location;
        $this->description  = $description;
        $this->applyUrl     = $applyUrl;
        $this->datePosted   = $datePosted;
    }

    /**
     * @throws \Exception
     */
    public function getId(): int {
        if ($this->id === null) {
            throw new \Exception('Job must be persisted before accessing its ID.');
        }

        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getCompanyName(): string {
        return $this->companyName;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getApplyUrl(): string {
        return $this->applyUrl;
    }

    public function getDatePosted(): \DateTime {
        return $this->datePosted;
    }
}
