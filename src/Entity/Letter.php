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
 * Entity class for "letters" table
 */

#[Entity]
#[Table("letters", options: ["dbId" => "DB"])]
class Letter extends AbstractEntity
{
    #[Id]
    #[Column(name: "letter_id", type: "integer", unique: true)]
    #[GeneratedValue]
    private int $LetterId;

    #[Column(name: "nomor_surat", type: "string", unique: true)]
    private string $NomorSurat;

    #[Column(name: "perihal", type: "string")]
    private string $Perihal;

    #[Column(name: "tanggal_surat", type: "date")]
    private DateTime $TanggalSurat;

    #[Column(name: "tanggal_terima", type: "date", nullable: true)]
    private ?DateTime $TanggalTerima;

    #[Column(name: "jenis", type: "string")]
    private string $Jenis;

    #[Column(name: "klasifikasi", type: "string")]
    private string $Klasifikasi;

    #[Column(name: "pengirim", type: "string")]
    private string $Pengirim;

    #[Column(name: "penerima_unit_id", type: "integer", nullable: true)]
    private ?int $PenerimaUnitId;

    #[Column(name: "file_url", type: "string")]
    private string $FileUrl;

    #[Column(name: "status", type: "string")]
    private string $Status;

    #[Column(name: "created_by", type: "integer")]
    private int $CreatedBy;

    #[Column(name: "created_at", type: "datetime")]
    private DateTime $CreatedAt;

    #[Column(name: "updated_at", type: "datetime")]
    private DateTime $UpdatedAt;

    public function getLetterId(): int
    {
        return $this->LetterId;
    }

    public function setLetterId(int $value): static
    {
        $this->LetterId = $value;
        return $this;
    }

    public function getNomorSurat(): string
    {
        return HtmlDecode($this->NomorSurat);
    }

    public function setNomorSurat(string $value): static
    {
        $this->NomorSurat = RemoveXss($value);
        return $this;
    }

    public function getPerihal(): string
    {
        return HtmlDecode($this->Perihal);
    }

    public function setPerihal(string $value): static
    {
        $this->Perihal = RemoveXss($value);
        return $this;
    }

    public function getTanggalSurat(): DateTime
    {
        return $this->TanggalSurat;
    }

    public function setTanggalSurat(DateTime $value): static
    {
        $this->TanggalSurat = $value;
        return $this;
    }

    public function getTanggalTerima(): ?DateTime
    {
        return $this->TanggalTerima;
    }

    public function setTanggalTerima(?DateTime $value): static
    {
        $this->TanggalTerima = $value;
        return $this;
    }

    public function getJenis(): string
    {
        return $this->Jenis;
    }

    public function setJenis(string $value): static
    {
        if (!in_array($value, ["masuk", "keluar"])) {
            throw new \InvalidArgumentException("Invalid 'jenis' value");
        }
        $this->Jenis = $value;
        return $this;
    }

    public function getKlasifikasi(): string
    {
        return $this->Klasifikasi;
    }

    public function setKlasifikasi(string $value): static
    {
        if (!in_array($value, ["biasa", "penting", "rahasia"])) {
            throw new \InvalidArgumentException("Invalid 'klasifikasi' value");
        }
        $this->Klasifikasi = $value;
        return $this;
    }

    public function getPengirim(): string
    {
        return HtmlDecode($this->Pengirim);
    }

    public function setPengirim(string $value): static
    {
        $this->Pengirim = RemoveXss($value);
        return $this;
    }

    public function getPenerimaUnitId(): ?int
    {
        return $this->PenerimaUnitId;
    }

    public function setPenerimaUnitId(?int $value): static
    {
        $this->PenerimaUnitId = $value;
        return $this;
    }

    public function getFileUrl(): string
    {
        return HtmlDecode($this->FileUrl);
    }

    public function setFileUrl(string $value): static
    {
        $this->FileUrl = RemoveXss($value);
        return $this;
    }

    public function getStatus(): string
    {
        return $this->Status;
    }

    public function setStatus(string $value): static
    {
        if (!in_array($value, ["draft", "terkirim", "disposisi", "selesai"])) {
            throw new \InvalidArgumentException("Invalid 'status' value");
        }
        $this->Status = $value;
        return $this;
    }

    public function getCreatedBy(): int
    {
        return $this->CreatedBy;
    }

    public function setCreatedBy(int $value): static
    {
        $this->CreatedBy = $value;
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

    public function getUpdatedAt(): DateTime
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(DateTime $value): static
    {
        $this->UpdatedAt = $value;
        return $this;
    }
}
