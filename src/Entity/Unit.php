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
 * Entity class for "units" table
 */

#[Entity]
#[Table("units", options: ["dbId" => "DB"])]
class Unit extends AbstractEntity
{
    #[Id]
    #[Column(name: "unit_id", type: "integer", unique: true)]
    #[GeneratedValue]
    private int $UnitId;

    #[Column(name: "nama_unit", type: "string")]
    private string $NamaUnit;

    #[Column(name: "kode_unit", type: "string", unique: true)]
    private string $KodeUnit;

    #[Column(name: "created_at", type: "datetime")]
    private DateTime $CreatedAt;

    public function getUnitId(): int
    {
        return $this->UnitId;
    }

    public function setUnitId(int $value): static
    {
        $this->UnitId = $value;
        return $this;
    }

    public function getNamaUnit(): string
    {
        return HtmlDecode($this->NamaUnit);
    }

    public function setNamaUnit(string $value): static
    {
        $this->NamaUnit = RemoveXss($value);
        return $this;
    }

    public function getKodeUnit(): string
    {
        return HtmlDecode($this->KodeUnit);
    }

    public function setKodeUnit(string $value): static
    {
        $this->KodeUnit = RemoveXss($value);
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
