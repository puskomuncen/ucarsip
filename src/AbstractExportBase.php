<?php

namespace PHPMaker2025\ucarsip;

use DateTime;

/**
 * Abstract base class for export
 */
abstract class AbstractExportBase
{
    protected ?string $FileId = null; // File ID for saving to folder
    public string $Text = ""; // Text or HTML to be exported
    public string $ContentType = ""; // Content type
    public bool $UseCharset = false; // Add charset to content type
    public bool $UseBom = false; // Output byte order mark
    public string $CacheControl = "no-store, no-cache"; // Cache control
    public string $FileName = ""; // User specified file name
    public string $FileExtension = ""; // File extension without "."
    public string $Disposition = "attachment"; // Disposition for Content-Disposition header or email attachment
    public ?string $Download = null;

    // Constructor
    public function __construct(
        protected ?DbTableBase $table = null // Table/Page object
    ) {
        $this->table = $table;
        $this->UseCharset = ConvertToBool(Get(Config("API_EXPORT_USE_CHARSET"), $this->UseCharset));
        $this->UseBom = ConvertToBool(Get(Config("API_EXPORT_USE_BOM"), $this->UseBom));
        $this->CacheControl = Get(Config("API_EXPORT_CACHE_CONTROL"), $this->CacheControl);
        $this->Disposition = Get(Config("API_EXPORT_DISPOSITION"), $this->Disposition);
        $this->Download = Get(Config("API_EXPORT_DOWNLOAD")); // Override $this->Disposition if not null
        $this->ContentType = Get(Config("API_EXPORT_CONTENT_TYPE"), $this->ContentType);
        $this->StyleSheet = Config("PROJECT_STYLESHEET_FILENAME");
        if (!$this->ContentType && $this->FileExtension) {
            $this->ContentType = MimeTypes()->getMimeTypes($this->FileExtension)[0];
        }
    }

    /**
     * Get table
     *
     * @return DbTableBase Table/Page object
     */
    public function getTable(): ?DbTableBase
    {
        return $this->table;
    }

    /**
     * Set table
     *
     * @param DbTableBase $value Table/Page object
     * @return static
     */
    public function setTable(?DbTableBase $value): static
    {
        $this->table = $value;
        return $this;
    }

    /**
     * Get file ID (GUID)
     *
     * @return string
     */
    public function getFileId(): string
    {
        return $this->FileId ??= NewGuid();
    }

    /**
     * Get save file name (<guid>.<ext>)
     *
     * @return string
     */
    public function getSaveFileName(): string
    {
        return $this->fixFileName($this->getFileId());
    }

    /**
     * Get Content-Type header
     *
     * @return string
     */
    public function contentTypeHeader(): string
    {
        $header = $this->ContentType;
        if ($this->UseCharset) {
            $header .= PROJECT_CHARSET != "" ? "; charset=" . PROJECT_CHARSET : "";
        }
        return $header;
    }

    /**
     * Get Content-Disposition header
     *
     * @param string $fileName File name
     * @return string
     */
    public function contentDispositionHeader(string $fileName = ""): string
    {
        $header = $this->getDisposition();
        if ($header == "attachment" && $fileName != "") {
            $header .= "; filename=\"" . $fileName . "\"";
        }
        return $header;
    }

    /**
     * Write BOM
     *
     * @return static
     */
    public function writeBom(): static
    {
        if ($this->UseBom) {
            echo "\xEF\xBB\xBF";
        }
        return $this;
    }

    /**
     * Write content
     *
     * @return static
     */
    public function write(): static
    {
        echo $this->Text;
        return $this;
    }

    /**
     * Get disposition
     *
     * @return string "inline" or "attachment"
     */
    public function getDisposition(): string
    {
        if ($this->Download !== null) {
            return ConvertToBool($this->Download) ? "attachment" : "inline";
        }
        $value = strtolower($this->Disposition);
        if (in_array($value, ["inline", "attachment"])) {
            return $value;
        }
        return "attachment";
    }

    /**
     * Fix file extension
     *
     * @param string $fileName File name
     * @return string
     */
    public function fixFileName(string $fileName): string
    {
        if (!$fileName) {
            $fileName = ($this->table ? $this->table->TableVar . "_" : "") . (new DateTime())->format("YmdHisu"); // Temporary file name
        }
        $pathinfo = pathinfo($fileName);
        $fileName .= SameText($pathinfo["extension"] ?? "", $this->FileExtension) ? "" : "." . $this->FileExtension;
        return $fileName;
    }

    /**
     * Clean output buffer, write headers and BOM before export
     *
     * @param string $fileName File name. If specified, it will override the
     * @return static
     */
    public function writeHeaders(string $fileName = ""): static
    {
        header("Content-Type: " . $this->contentTypeHeader());
        header("Content-Disposition: " . $this->contentDispositionHeader($this->FileName ?: $fileName)); // Use $this->FileName specified by user first
        header("Cache-Control: " . $this->CacheControl);
        return $this->writeDebugHeaders()
            ->cleanBuffer()
            ->writeBom();
    }

    /**
     * Clean buffer
     *
     * @return static
     */
    public function cleanBuffer(): static
    {
        if (!IsDebug() && ob_get_length()) {
            ob_end_clean();
        }
        return $this;
    }

    /**
     * Write headers for debug bar
     *
     * @return static
     */
    public function writeDebugHeaders(): static
    {
        if (IsDebug()) {
            DebugBar()?->sendDataInHeaders();
        }
        return $this;
    }

    /**
     * Import data from table/page object
     *
     * @return void
     */
    public function import(): void
    {
        if (method_exists($this->table, "exportData")) {
            $this->table->exportData($this);
        }
    }

    /**
     * Export
     *
     * @param string $fileName Output file name
     * @param bool $output Whether output to browser
     * @param bool $save Whether save to folder
     * @return mixed
     */
    abstract public function export(string $fileName = "", bool $output = true, bool $save = false): mixed;
}
