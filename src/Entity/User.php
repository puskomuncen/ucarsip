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
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
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
 * Entity class for "users" table
 */

#[Entity(repositoryClass: UserRepository::class)]
#[Table("users", options: ["dbId" => "DB"])]
class User extends AbstractEntity implements AdvancedUserInterface, EquatableInterface, PasswordAuthenticatedUserInterface
{
    #[Id]
    #[Column(name: "`UserID`", options: ["name" => "UserID"], type: "integer", unique: true)]
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
        return $this->Username;
    }

    public function setUsername(string $value): static
    {
        $this->Username = $value;
        return $this;
    }

    public function get_Password(): ?string
    {
        return $this->_Password;
    }

    public function set_Password(?string $value): static
    {
        $this->_Password = HashPassword($value);
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

    public function getProfile(): ?array
    {
        return UserProfile::unserialize(HtmlDecode($this->Profile));
    }

    public function setProfile(?array $value): static
    {
        $this->Profile = RemoveXss(json_encode($value));
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

    /**
     * Get user name
     *
     * @return string
     */
    public function userName(): string
    {
        return $this->get('Username');
    }

    /**
     * Get user ID
     *
     * @return mixed
     */
    public function userId(): mixed
    {
        return $this->get('UserID');
    }

    /**
     * Get parent user ID
     *
     * @return mixed
     */
    public function parentUserId(): mixed
    {
        return $this->get('ReportsTo');
    }

    /**
     * Get user level
     *
     * @return int|string
     */
    public function userLevel(): int|string
    {
        return $this->get('UserLevel') ?? AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID;
    }

    /**
     * Roles
     */
    protected array $roles = ['ROLE_USER'];

    /**
     * Get the roles granted to the user, e.g. ['ROLE_USER']
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $userLevelId = $this->get('UserLevel');
        $roles = Security()->getAllRoles($userLevelId);
        return array_unique([...$this->roles, ...$roles]);
    }

    /**
     * Add a role
     *
     * @param string $role Role
     * @return void
     */
    public function addRole(string $role): void
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
    }

    /**
     * Remove a role
     *
     * @param string $role Role
     * @return void
     */
    public function removeRole(string $role): void
    {
        if (in_array($role, $this->roles)) {
            unset($this->roles[$role]);
        }
    }

    /**
     * Remove sensitive data from the user
     */
    public function eraseCredentials(): void
    {
        // Don't erase
    }

    /**
     * Get the identifier for this user (e.g. username or email address)
     */
    public function getUserIdentifier(): string
    {
        return $this->Username;
    }

    /**
     * Get the hashed password for this user
     */
    public function getPassword(): ?string
    {
        return $this->_Password;
    }

    /**
     * Compare users by attributes that are relevant for assessing whether re-authentication is required
     * See https://symfony.com/doc/current/security.html#understanding-how-users-are-refreshed-from-the-session
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        // if ($this->getPassword() !== $user->getPassword()) {
        //     return false;
        // }
        $currentRoles = array_map("strval", (array) $this->getRoles());
        $newRoles = array_map("strval", (array) $user->getRoles());
        $rolesChanged = count($currentRoles) !== count($newRoles) || count($currentRoles) !== count(array_intersect($currentRoles, $newRoles));
        if ($rolesChanged) {
            return false;
        }
        if ($this->getUserIdentifier() !== $user->getUserIdentifier()) {
            return false;
        }
        return true;
    }
}
