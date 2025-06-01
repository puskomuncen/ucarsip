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
 * Entity class for "theuserprofile" table
 */

#[Entity]
#[Table("theuserprofile", options: ["dbId" => "DB"])]
class Theuserprofile extends AbstractEntity
{
    #[Id]
    #[Column(name: "`UserID`", options: ["name" => "UserID"], type: "integer")]
    #[GeneratedValue]
    private int $UserId;

    #[Column(type: "string")]
    private string $Username;

    #[Column(name: "`Password`", options: ["name" => "Password"], type: "string", nullable: true)]
    private ?string $_Password;

    #[Column(type: "integer", nullable: true)]
    private ?int $UserLevel;

    #[Column(type: "string")]
    private string $FirstName;

    #[Column(type: "string", nullable: true)]
    private ?string $LastName;

    #[Column(type: "string")]
    private string $CompleteName;

    #[Column(type: "datetime", nullable: true)]
    private ?DateTime $BirthDate;

    #[Column(type: "string", nullable: true)]
    private ?string $HomePhone;

    #[Column(type: "string", nullable: true)]
    private ?string $Photo;

    #[Column(type: "text", nullable: true)]
    private ?string $Notes;

    #[Column(type: "integer", nullable: true)]
    private ?int $ReportsTo;

    #[Column(type: "string")]
    private string $Gender;

    #[Column(type: "string", nullable: true)]
    private ?string $Email;

    #[Column(type: "string", nullable: true)]
    private ?string $Activated;

    #[Column(type: "text", nullable: true)]
    private ?string $Profile;

    #[Column(type: "string", nullable: true)]
    private ?string $Avatar;

    #[Column(type: "boolean", nullable: true)]
    private ?bool $ActiveStatus;

    #[Column(type: "string", nullable: true)]
    private ?string $MessengerColor;

    #[Column(type: "datetime", nullable: true)]
    private ?DateTime $CreatedAt;

    #[Column(type: "string", nullable: true)]
    private ?string $CreatedBy;

    #[Column(type: "datetime", nullable: true)]
    private ?DateTime $UpdatedAt;

    #[Column(type: "string", nullable: true)]
    private ?string $UpdatedBy;

    public function __construct()
    {
        $this->UserLevel = 0;
        $this->CreatedBy = CurrentUserName();
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

    public function getUsername(): string
    {
        return HtmlDecode($this->Username);
    }

    public function setUsername(string $value): static
    {
        $this->Username = RemoveXss($value);
        return $this;
    }

    public function get_Password(): ?string
    {
        return HtmlDecode($this->_Password);
    }

    public function set_Password(?string $value): static
    {
        $this->_Password = RemoveXss($value);
        return $this;
    }

    public function getUserLevel(): ?int
    {
        return $this->UserLevel;
    }

    public function setUserLevel(?int $value): static
    {
        $this->UserLevel = $value;
        return $this;
    }

    public function getFirstName(): string
    {
        return HtmlDecode($this->FirstName);
    }

    public function setFirstName(string $value): static
    {
        $this->FirstName = RemoveXss($value);
        return $this;
    }

    public function getLastName(): ?string
    {
        return HtmlDecode($this->LastName);
    }

    public function setLastName(?string $value): static
    {
        $this->LastName = RemoveXss($value);
        return $this;
    }

    public function getCompleteName(): string
    {
        return HtmlDecode($this->CompleteName);
    }

    public function setCompleteName(string $value): static
    {
        $this->CompleteName = RemoveXss($value);
        return $this;
    }

    public function getBirthDate(): ?DateTime
    {
        return $this->BirthDate;
    }

    public function setBirthDate(?DateTime $value): static
    {
        $this->BirthDate = $value;
        return $this;
    }

    public function getHomePhone(): ?string
    {
        return HtmlDecode($this->HomePhone);
    }

    public function setHomePhone(?string $value): static
    {
        $this->HomePhone = RemoveXss($value);
        return $this;
    }

    public function getPhoto(): ?string
    {
        return HtmlDecode($this->Photo);
    }

    public function setPhoto(?string $value): static
    {
        $this->Photo = RemoveXss($value);
        return $this;
    }

    public function getNotes(): ?string
    {
        return HtmlDecode($this->Notes);
    }

    public function setNotes(?string $value): static
    {
        $this->Notes = RemoveXss($value);
        return $this;
    }

    public function getReportsTo(): ?int
    {
        return $this->ReportsTo;
    }

    public function setReportsTo(?int $value): static
    {
        $this->ReportsTo = $value;
        return $this;
    }

    public function getGender(): string
    {
        return HtmlDecode($this->Gender);
    }

    public function setGender(string $value): static
    {
        $this->Gender = RemoveXss($value);
        return $this;
    }

    public function getEmail(): ?string
    {
        return HtmlDecode($this->Email);
    }

    public function setEmail(?string $value): static
    {
        $this->Email = RemoveXss($value);
        return $this;
    }

    public function getActivated(): ?string
    {
        return $this->Activated;
    }

    public function setActivated(?string $value): static
    {
        if (!in_array($value, ["Y", "N"])) {
            throw new \InvalidArgumentException("Invalid 'Activated' value");
        }
        $this->Activated = $value;
        return $this;
    }

    public function getProfile(): ?string
    {
        return HtmlDecode($this->Profile);
    }

    public function setProfile(?string $value): static
    {
        $this->Profile = RemoveXss($value);
        return $this;
    }

    public function getAvatar(): ?string
    {
        return HtmlDecode($this->Avatar);
    }

    public function setAvatar(?string $value): static
    {
        $this->Avatar = RemoveXss($value);
        return $this;
    }

    public function getActiveStatus(): ?bool
    {
        return $this->ActiveStatus;
    }

    public function setActiveStatus(?bool $value): static
    {
        $this->ActiveStatus = $value;
        return $this;
    }

    public function getMessengerColor(): ?string
    {
        return HtmlDecode($this->MessengerColor);
    }

    public function setMessengerColor(?string $value): static
    {
        $this->MessengerColor = RemoveXss($value);
        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(?DateTime $value): static
    {
        $this->CreatedAt = $value;
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

    public function getUpdatedAt(): ?DateTime
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(?DateTime $value): static
    {
        $this->UpdatedAt = $value;
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
}
