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
 * Entity class for "settings" table
 */

#[Entity]
#[Table("settings", options: ["dbId" => "DB"])]
class Setting extends AbstractEntity
{
    #[Id]
    #[Column(name: "`Option_ID`", options: ["name" => "Option_ID"], type: "integer", unique: true)]
    #[GeneratedValue]
    private int $OptionId;

    #[Column(name: "`Option_Default`", options: ["name" => "Option_Default"], type: "string", nullable: true)]
    private ?string $OptionDefault;

    #[Column(name: "`Show_Announcement`", options: ["name" => "Show_Announcement"], type: "string", nullable: true)]
    private ?string $ShowAnnouncement;

    #[Column(name: "`Use_Announcement_Table`", options: ["name" => "Use_Announcement_Table"], type: "string", nullable: true)]
    private ?string $UseAnnouncementTable;

    #[Column(name: "`Maintenance_Mode`", options: ["name" => "Maintenance_Mode"], type: "string", nullable: true)]
    private ?string $MaintenanceMode;

    #[Column(name: "`Maintenance_Finish_DateTime`", options: ["name" => "Maintenance_Finish_DateTime"], type: "datetime", nullable: true)]
    private ?DateTime $MaintenanceFinishDateTime;

    #[Column(name: "`Auto_Normal_After_Maintenance`", options: ["name" => "Auto_Normal_After_Maintenance"], type: "string", nullable: true)]
    private ?string $AutoNormalAfterMaintenance;

    public function __construct()
    {
        $this->OptionDefault = "N";
        $this->ShowAnnouncement = "N";
        $this->UseAnnouncementTable = "N";
        $this->MaintenanceMode = "N";
        $this->AutoNormalAfterMaintenance = "Y";
    }

    public function getOptionId(): int
    {
        return $this->OptionId;
    }

    public function setOptionId(int $value): static
    {
        $this->OptionId = $value;
        return $this;
    }

    public function getOptionDefault(): ?string
    {
        return $this->OptionDefault;
    }

    public function setOptionDefault(?string $value): static
    {
        if (!in_array($value, ["Y", "N"])) {
            throw new \InvalidArgumentException("Invalid 'Option_Default' value");
        }
        $this->OptionDefault = $value;
        return $this;
    }

    public function getShowAnnouncement(): ?string
    {
        return $this->ShowAnnouncement;
    }

    public function setShowAnnouncement(?string $value): static
    {
        if (!in_array($value, ["Y", "N"])) {
            throw new \InvalidArgumentException("Invalid 'Show_Announcement' value");
        }
        $this->ShowAnnouncement = $value;
        return $this;
    }

    public function getUseAnnouncementTable(): ?string
    {
        return $this->UseAnnouncementTable;
    }

    public function setUseAnnouncementTable(?string $value): static
    {
        if (!in_array($value, ["N", "Y"])) {
            throw new \InvalidArgumentException("Invalid 'Use_Announcement_Table' value");
        }
        $this->UseAnnouncementTable = $value;
        return $this;
    }

    public function getMaintenanceMode(): ?string
    {
        return $this->MaintenanceMode;
    }

    public function setMaintenanceMode(?string $value): static
    {
        if (!in_array($value, ["N", "Y"])) {
            throw new \InvalidArgumentException("Invalid 'Maintenance_Mode' value");
        }
        $this->MaintenanceMode = $value;
        return $this;
    }

    public function getMaintenanceFinishDateTime(): ?DateTime
    {
        return $this->MaintenanceFinishDateTime;
    }

    public function setMaintenanceFinishDateTime(?DateTime $value): static
    {
        $this->MaintenanceFinishDateTime = $value;
        return $this;
    }

    public function getAutoNormalAfterMaintenance(): ?string
    {
        return $this->AutoNormalAfterMaintenance;
    }

    public function setAutoNormalAfterMaintenance(?string $value): static
    {
        if (!in_array($value, ["Y", "N"])) {
            throw new \InvalidArgumentException("Invalid 'Auto_Normal_After_Maintenance' value");
        }
        $this->AutoNormalAfterMaintenance = $value;
        return $this;
    }
}
