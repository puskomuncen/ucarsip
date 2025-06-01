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
 * Entity class for "languages" table
 */

#[Entity]
#[Table("languages", options: ["dbId" => "DB"])]
class Language extends AbstractEntity
{
    #[Id]
    #[Column(name: "`Language_Code`", options: ["name" => "Language_Code"], type: "string", unique: true)]
    private string $LanguageCode;

    #[Column(name: "`Language_Name`", options: ["name" => "Language_Name"], type: "string")]
    private string $LanguageName;

    #[Column(name: "`Default`", options: ["name" => "Default"], type: "string", nullable: true)]
    private ?string $Default;

    #[Column(name: "`Site_Logo`", options: ["name" => "Site_Logo"], type: "string")]
    private string $SiteLogo;

    #[Column(name: "`Site_Title`", options: ["name" => "Site_Title"], type: "string")]
    private string $SiteTitle;

    #[Column(name: "`Default_Thousands_Separator`", options: ["name" => "Default_Thousands_Separator"], type: "string", nullable: true)]
    private ?string $DefaultThousandsSeparator;

    #[Column(name: "`Default_Decimal_Point`", options: ["name" => "Default_Decimal_Point"], type: "string", nullable: true)]
    private ?string $DefaultDecimalPoint;

    #[Column(name: "`Default_Currency_Symbol`", options: ["name" => "Default_Currency_Symbol"], type: "string", nullable: true)]
    private ?string $DefaultCurrencySymbol;

    #[Column(name: "`Default_Money_Thousands_Separator`", options: ["name" => "Default_Money_Thousands_Separator"], type: "string", nullable: true)]
    private ?string $DefaultMoneyThousandsSeparator;

    #[Column(name: "`Default_Money_Decimal_Point`", options: ["name" => "Default_Money_Decimal_Point"], type: "string", nullable: true)]
    private ?string $DefaultMoneyDecimalPoint;

    #[Column(name: "`Terms_And_Condition_Text`", options: ["name" => "Terms_And_Condition_Text"], type: "text")]
    private string $TermsAndConditionText;

    #[Column(name: "`Announcement_Text`", options: ["name" => "Announcement_Text"], type: "text")]
    private string $AnnouncementText;

    #[Column(name: "`About_Text`", options: ["name" => "About_Text"], type: "text")]
    private string $AboutText;

    public function __construct()
    {
        $this->Default = "N";
    }

    public function getLanguageCode(): string
    {
        return $this->LanguageCode;
    }

    public function setLanguageCode(string $value): static
    {
        $this->LanguageCode = $value;
        return $this;
    }

    public function getLanguageName(): string
    {
        return HtmlDecode($this->LanguageName);
    }

    public function setLanguageName(string $value): static
    {
        $this->LanguageName = RemoveXss($value);
        return $this;
    }

    public function getDefault(): ?string
    {
        return $this->Default;
    }

    public function setDefault(?string $value): static
    {
        if (!in_array($value, ["Y", "N"])) {
            throw new \InvalidArgumentException("Invalid 'Default' value");
        }
        $this->Default = $value;
        return $this;
    }

    public function getSiteLogo(): string
    {
        return HtmlDecode($this->SiteLogo);
    }

    public function setSiteLogo(string $value): static
    {
        $this->SiteLogo = RemoveXss($value);
        return $this;
    }

    public function getSiteTitle(): string
    {
        return HtmlDecode($this->SiteTitle);
    }

    public function setSiteTitle(string $value): static
    {
        $this->SiteTitle = RemoveXss($value);
        return $this;
    }

    public function getDefaultThousandsSeparator(): ?string
    {
        return HtmlDecode($this->DefaultThousandsSeparator);
    }

    public function setDefaultThousandsSeparator(?string $value): static
    {
        $this->DefaultThousandsSeparator = RemoveXss($value);
        return $this;
    }

    public function getDefaultDecimalPoint(): ?string
    {
        return HtmlDecode($this->DefaultDecimalPoint);
    }

    public function setDefaultDecimalPoint(?string $value): static
    {
        $this->DefaultDecimalPoint = RemoveXss($value);
        return $this;
    }

    public function getDefaultCurrencySymbol(): ?string
    {
        return HtmlDecode($this->DefaultCurrencySymbol);
    }

    public function setDefaultCurrencySymbol(?string $value): static
    {
        $this->DefaultCurrencySymbol = RemoveXss($value);
        return $this;
    }

    public function getDefaultMoneyThousandsSeparator(): ?string
    {
        return HtmlDecode($this->DefaultMoneyThousandsSeparator);
    }

    public function setDefaultMoneyThousandsSeparator(?string $value): static
    {
        $this->DefaultMoneyThousandsSeparator = RemoveXss($value);
        return $this;
    }

    public function getDefaultMoneyDecimalPoint(): ?string
    {
        return HtmlDecode($this->DefaultMoneyDecimalPoint);
    }

    public function setDefaultMoneyDecimalPoint(?string $value): static
    {
        $this->DefaultMoneyDecimalPoint = RemoveXss($value);
        return $this;
    }

    public function getTermsAndConditionText(): string
    {
        return HtmlDecode($this->TermsAndConditionText);
    }

    public function setTermsAndConditionText(string $value): static
    {
        $this->TermsAndConditionText = RemoveXss($value);
        return $this;
    }

    public function getAnnouncementText(): string
    {
        return HtmlDecode($this->AnnouncementText);
    }

    public function setAnnouncementText(string $value): static
    {
        $this->AnnouncementText = RemoveXss($value);
        return $this;
    }

    public function getAboutText(): string
    {
        return HtmlDecode($this->AboutText);
    }

    public function setAboutText(string $value): static
    {
        $this->AboutText = RemoveXss($value);
        return $this;
    }
}
