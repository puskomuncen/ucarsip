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
 * Entity class for "help" table
 */

#[Entity]
#[Table("help", options: ["dbId" => "DB"])]
class Help extends AbstractEntity
{
    #[Id]
    #[Column(name: "`Help_ID`", options: ["name" => "Help_ID"], type: "integer", unique: true)]
    #[GeneratedValue]
    private int $HelpId;

    #[Column(type: "string")]
    private string $Language;

    #[Column(type: "string")]
    private string $Topic;

    #[Column(type: "text")]
    private string $Description;

    #[Column(type: "integer")]
    private int $Category;

    #[Column(name: "`Order`", options: ["name" => "Order"], type: "integer")]
    private int $Order;

    #[Column(name: "`Display_in_Page`", options: ["name" => "Display_in_Page"], type: "string")]
    private string $DisplayInPage;

    #[Column(name: "`Updated_By`", options: ["name" => "Updated_By"], type: "string", nullable: true)]
    private ?string $UpdatedBy;

    #[Column(name: "`Last_Updated`", options: ["name" => "Last_Updated"], type: "datetime", nullable: true)]
    private ?DateTime $LastUpdated;

    public function getHelpId(): int
    {
        return $this->HelpId;
    }

    public function setHelpId(int $value): static
    {
        $this->HelpId = $value;
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

    public function getTopic(): string
    {
        return HtmlDecode($this->Topic);
    }

    public function setTopic(string $value): static
    {
        $this->Topic = RemoveXss($value);
        return $this;
    }

    public function getDescription(): string
    {
        return HtmlDecode($this->Description);
    }

    public function setDescription(string $value): static
    {
        $this->Description = RemoveXss($value);
        return $this;
    }

    public function getCategory(): int
    {
        return $this->Category;
    }

    public function setCategory(int $value): static
    {
        $this->Category = $value;
        return $this;
    }

    public function getOrder(): int
    {
        return $this->Order;
    }

    public function setOrder(int $value): static
    {
        $this->Order = $value;
        return $this;
    }

    public function getDisplayInPage(): string
    {
        return HtmlDecode($this->DisplayInPage);
    }

    public function setDisplayInPage(string $value): static
    {
        $this->DisplayInPage = RemoveXss($value);
        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return HtmlDecode($this->UpdatedBy);
    }

    public function setUpdatedBy(?string $value): static
    {
        $this->UpdatedBy = RemoveXss($value);
        return $this;
    }

    public function getLastUpdated(): ?DateTime
    {
        return $this->LastUpdated;
    }

    public function setLastUpdated(?DateTime $value): static
    {
        $this->LastUpdated = $value;
        return $this;
    }
}
