<?php

namespace PHPMaker2025\ucarsip;

/**
 * Export to XML
 */
class ExportXml extends AbstractExport
{
    public static $NullString = "null";
    public bool $HasParent;
    public string $FileExtension = "xml";
    public string $Disposition = "inline";

    // Constructor
    public function __construct(?DbTableBase $table = null, public XmlDocument $XmlDoc = new XmlDocument()) // Always utf-8
    {
        parent::__construct($table);
    }

    // Style
    public function setStyle(string $style): void
    {
    }

    // Field caption
    public function exportCaption(DbField $fld): void
    {
    }

    // Field value
    public function exportValue(DbField $fld): void
    {
    }

    // Field aggregate
    public function exportAggregate(DbField $fld, string $type): void
    {
    }

    // Table header
    public function exportTableHeader(): void
    {
        $this->HasParent = is_object($this->XmlDoc->documentElement());
        if (!$this->HasParent) {
            $this->XmlDoc->addRoot($this->table->TableVar);
        }
    }

    // Export a value (caption, field value, or aggregate)
    protected function exportValueEx(DbField $fld, mixed $val): void
    {
    }

    // Begin a row
    public function beginExportRow(int $rowCnt = 0): void
    {
        if ($rowCnt <= 0) {
            return;
        }
        if ($this->HasParent) {
            $this->XmlDoc->addRow($this->table->TableVar);
        } else {
            $this->XmlDoc->addRow();
        }
    }

    // End a row
    public function endExportRow(int $rowCnt = 0): void
    {
    }

    // Empty row
    public function exportEmptyRow(): void
    {
    }

    // Page break
    public function exportPageBreak(): void
    {
    }

    // Export a field
    public function exportField(DbField $fld): void
    {
        if ($fld->Exportable && $fld->DataType != DataType::BLOB) {
            if ($fld->UploadMultiple) {
                $exportValue = $fld->Upload->DbValue;
            } else {
                $exportValue = $fld->exportValue();
            }
            if ($exportValue === null) {
                $exportValue = self::$NullString;
            }
            $this->XmlDoc->addField($fld->Param, $exportValue);
        }
    }

    // Table Footer
    public function exportTableFooter(): void
    {
    }

    // Add HTML tags
    public function exportHeaderAndFooter(): void
    {
    }

    // Export
    public function export(string $fileName = "", bool $output = true, bool $save = false): mixed
    {
        $this->Text = $this->XmlDoc->xml();
        if ($save) { // Save to folder
            WriteFile(ExportPath() . $this->getSaveFileName(), $this->Text);
        }
        if ($output) { // Output
            $this->writeHeaders($fileName);
            $this->write();
        }
        return null;
    }
}
