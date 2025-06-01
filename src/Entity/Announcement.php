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
 * Entity class for "announcement" table
 */

#[Entity]
#[Table("announcement", options: ["dbId" => "DB"])]
class Announcement extends AbstractEntity
{
    #[Id]
    #[Column(name: "`Announcement_ID`", options: ["name" => "Announcement_ID"], type: "integer", unique: true)]
    #[GeneratedValue]
    private int $AnnouncementId;

    #[Column(name: "`Is_Active`", options: ["name" => "Is_Active"], type: "string")]
    private string $IsActive;

    #[Column(type: "string")]
    private string $Topic;

    #[Column(type: "text")]
    private string $Message;

    #[Column(name: "`Date_LastUpdate`", options: ["name" => "Date_LastUpdate"], type: "datetime")]
    private DateTime $DateLastUpdate;

    #[Column(type: "string")]
    private string $Language;

    #[Column(name: "`Auto_Publish`", options: ["name" => "Auto_Publish"], type: "string", nullable: true)]
    private ?string $AutoPublish;

    #[Column(name: "`Date_Start`", options: ["name" => "Date_Start"], type: "datetime", nullable: true)]
    private ?DateTime $DateStart;

    #[Column(name: "`Date_End`", options: ["name" => "Date_End"], type: "datetime", nullable: true)]
    private ?DateTime $DateEnd;

    #[Column(name: "`Date_Created`", options: ["name" => "Date_Created"], type: "datetime", nullable: true)]
    private ?DateTime $DateCreated;

    #[Column(name: "`Created_By`", options: ["name" => "Created_By"], type: "string", nullable: true)]
    private ?string $CreatedBy;

    #[Column(name: "`Translated_ID`", options: ["name" => "Translated_ID"], type: "integer", nullable: true)]
    private ?int $TranslatedId;

    public function __construct()
    {
        $this->IsActive = "N";
        $this->Language = "en";
        $this->AutoPublish = "N";
    }

    public function getAnnouncementId(): int
    {
        return $this->AnnouncementId;
    }

    public function setAnnouncementId(int $value): static
    {
        $this->AnnouncementId = $value;
        return $this;
    }

    public function getIsActive(): string
    {
        return $this->IsActive;
    }

    public function setIsActive(string $value): static
    {
        if (!in_array($value, ["N", "Y"])) {
            throw new \InvalidArgumentException("Invalid 'Is_Active' value");
        }
        $this->IsActive = $value;
        return $this;
    }

    public function getTopic(): string
    {
        return HtmlDecode($this->Topic);
    }

    public function setTopic(string $value): static
    {
        $this->Topic = RemoveXss($value);
        return $this;
    }

    public function getMessage(): string
    {
        return HtmlDecode($this->Message);
    }

    public function setMessage(string $value): static
    {
        $this->Message = RemoveXss($value);
        return $this;
    }

    public function getDateLastUpdate(): DateTime
    {
        return $this->DateLastUpdate;
    }

    public function setDateLastUpdate(DateTime $value): static
    {
        $this->DateLastUpdate = $value;
        return $this;
    }

    public function getLanguage(): string
    {
        return HtmlDecode($this->Language);
    }

    public function setLanguage(string $value): static
    {
        $this->Language = RemoveXss($value);
        return $this;
    }

    public function getAutoPublish(): ?string
    {
        return $this->AutoPublish;
    }

    public function setAutoPublish(?string $value): static
    {
        if (!in_array($value, ["Y", "N"])) {
            throw new \InvalidArgumentException("Invalid 'Auto_Publish' value");
        }
        $this->AutoPublish = $value;
        return $this;
    }

    public function getDateStart(): ?DateTime
    {
        return $this->DateStart;
    }

    public function setDateStart(?DateTime $value): static
    {
        $this->DateStart = $value;
        return $this;
    }

    public function getDateEnd(): ?DateTime
    {
        return $this->DateEnd;
    }

    public function setDateEnd(?DateTime $value): static
    {
        $this->DateEnd = $value;
        return $this;
    }

    public function getDateCreated(): ?DateTime
    {
        return $this->DateCreated;
    }

    public function setDateCreated(?DateTime $value): static
    {
        $this->DateCreated = $value;
        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return HtmlDecode($this->CreatedBy);
    }

    public function setCreatedBy(?string $value): static
    {
        $this->CreatedBy = RemoveXss($value);
        return $this;
    }

    public function getTranslatedId(): ?int
    {
        return $this->TranslatedId;
    }

    public function setTranslatedId(?int $value): static
    {
        $this->TranslatedId = $value;
        return $this;
    }
}
