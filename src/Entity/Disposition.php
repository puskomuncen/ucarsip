<?php

namespace PHPMaker2025\ucarsip\Entity;

use DateTime;
use DateTimeImmutable;
use DateInterval;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\SequenceGenerator;
use Doctrine\DBAL\Types\Types;
use PHPMaker2025\ucarsip\AdvancedUserInterface;
use PHPMaker2025\ucarsip\AbstractEntity;
use PHPMaker2025\ucarsip\AdvancedSecurity;
use PHPMaker2025\ucarsip\UserProfile;
use PHPMaker2025\ucarsip\UserRepository;
use function PHPMaker2025\ucarsip\Config;
use function PHPMaker2025\ucarsip\EntityManager;
use function PHPMaker2025\ucarsip\RemoveXss;
use function PHPMaker2025\ucarsip\HtmlDecode;
use function PHPMaker2025\ucarsip\HashPassword;
use function PHPMaker2025\ucarsip\Security;

/**
 * Entity class for "dispositions" table
 */

#[Entity]
#[Table("dispositions", options: ["dbId" => "DB"])]
class Disposition extends AbstractEntity
{
    #[Id]
    #[Column(name: "disposition_id", type: "integer", unique: true)]
    #[GeneratedValue]
    private int $DispositionId;

    #[Column(name: "letter_id", type: "integer")]
    private int $LetterId;

    #[Column(name: "dari_unit_id", type: "integer")]
    private int $DariUnitId;

    #[Column(name: "ke_unit_id", type: "integer")]
    private int $KeUnitId;

    #[Column(name: "catatan", type: "text", nullable: true)]
    private ?string $Catatan;

    #[Column(name: "status", type: "string")]
    private string $Status;

    #[Column(name: "created_at", type: "datetime")]
    private DateTime $CreatedAt;

    public function getDispositionId(): int
    {
        return $this->DispositionId;
    }

    public function setDispositionId(int $value): static
    {
        $this->DispositionId = $value;
        return $this;
    }

    public function getLetterId(): int
    {
        return $this->LetterId;
    }

    public function setLetterId(int $value): static
    {
        $this->LetterId = $value;
        return $this;
    }

    public function getDariUnitId(): int
    {
        return $this->DariUnitId;
    }

    public function setDariUnitId(int $value): static
    {
        $this->DariUnitId = $value;
        return $this;
    }

    public function getKeUnitId(): int
    {
        return $this->KeUnitId;
    }

    public function setKeUnitId(int $value): static
    {
        $this->KeUnitId = $value;
        return $this;
    }

    public function getCatatan(): ?string
    {
        return HtmlDecode($this->Catatan);
    }

    public function setCatatan(?string $value): static
    {
        $this->Catatan = RemoveXss($value);
        return $this;
    }

    public function getStatus(): string
    {
        return $this->Status;
    }

    public function setStatus(string $value): static
    {
        if (!in_array($value, ["diterima", "ditolak", "diproses"])) {
            throw new \InvalidArgumentException("Invalid 'status' value");
        }
        $this->Status = $value;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(DateTime $value): static
    {
        $this->CreatedAt = $value;
        return $this;
    }
}
