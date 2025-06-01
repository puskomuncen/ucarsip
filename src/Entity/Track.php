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
 * Entity class for "tracks" table
 */

#[Entity]
#[Table("tracks", options: ["dbId" => "DB"])]
class Track extends AbstractEntity
{
    #[Id]
    #[Column(name: "track_id", type: "integer", unique: true)]
    #[GeneratedValue]
    private int $TrackId;

    #[Column(name: "letter_id", type: "integer")]
    private int $LetterId;

    #[Column(name: "user_id", type: "integer")]
    private int $UserId;

    #[Column(name: "action", type: "string")]
    private string $Action;

    #[Column(name: "keterangan", type: "text", nullable: true)]
    private ?string $Keterangan;

    #[Column(name: "created_at", type: "datetime")]
    private DateTime $CreatedAt;

    public function getTrackId(): int
    {
        return $this->TrackId;
    }

    public function setTrackId(int $value): static
    {
        $this->TrackId = $value;
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

    public function getUserId(): int
    {
        return $this->UserId;
    }

    public function setUserId(int $value): static
    {
        $this->UserId = $value;
        return $this;
    }

    public function getAction(): string
    {
        return $this->Action;
    }

    public function setAction(string $value): static
    {
        if (!in_array($value, ["create", "update", "disposisi", "selesai"])) {
            throw new \InvalidArgumentException("Invalid 'action' value");
        }
        $this->Action = $value;
        return $this;
    }

    public function getKeterangan(): ?string
    {
        return HtmlDecode($this->Keterangan);
    }

    public function setKeterangan(?string $value): static
    {
        $this->Keterangan = RemoveXss($value);
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
