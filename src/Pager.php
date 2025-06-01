<?php

namespace PHPMaker2025\ucarsip;

/**
 * Pager class
 */
class Pager
{
    public ?PagerItem $NextButton = null;
    public ?PagerItem $FirstButton = null;
    public ?PagerItem $PrevButton = null;
    public ?PagerItem $LastButton = null;
    public int $PageSize = 0;
    public int $FromIndex = 0;
    public int $ToIndex = 0;
    public int $RecordCount = 0;
    public int $Range = 10;
    public bool $Visible = true;
    public bool $AutoHidePager = true;
    public bool $AutoHidePageSizeSelector = true;
    public bool $UsePageSizeSelector = true;
    public string $PageSizes = "";
    public string $ItemPhraseId = "Record";
    public ?DbTableBase $Table = null;
    public string $PageNumberName = "";
    public string $PagePhraseId = "Page";
    public string $ContextClass = "";
    private bool $PageSizeAll = false; // Handle page size = -1 (ALL)
    public static $FormatIntegerFunc = PROJECT_NAMESPACE . "FormatInteger";

    // Constructor
    public function __construct(
        ?DbTableBase $table,
        int $fromIndex,
        int $pageSize,
        int $recordCount,
        string $pageSizes = "",
        int $range = 10,
        ?bool $autoHidePager = null,
        ?bool $autoHidePageSizeSelector = null,
        ?bool $usePageSizeSelector = null)
    {
        $this->Table = $table;
        $this->ContextClass = CheckClassName($this->Table->TableVar);
        $this->AutoHidePager = $autoHidePager === null ? Config("AUTO_HIDE_PAGER") : $autoHidePager;
        $this->AutoHidePageSizeSelector = $autoHidePageSizeSelector === null ? Config("AUTO_HIDE_PAGE_SIZE_SELECTOR") : $autoHidePageSizeSelector;
        $this->UsePageSizeSelector = $usePageSizeSelector === null ? true : $usePageSizeSelector;
        $this->FromIndex = (int)$fromIndex;
        $this->PageSize = (int)$pageSize;
        $this->RecordCount = (int)$recordCount;
        $this->Range = (int)$range;
        $this->PageSizes = $pageSizes;
        // Handle page size = 0
        if ($this->PageSize == 0) {
            $this->PageSize = $this->RecordCount > 0 ? $this->RecordCount : 10;
        }
        // Handle page size = -1 (ALL)
        if ($this->PageSize == -1 || $this->PageSize == $this->RecordCount) {
            $this->PageSizeAll = true;
            $this->PageSize = $this->RecordCount > 0 ? $this->RecordCount : 10;
        }
        $this->PageNumberName = Config("TABLE_PAGE_NUMBER");
    }

    // Is visible
    public function isVisible(): bool
    {
        return $this->RecordCount > 0 && $this->Visible;
    }

    // Render
    public function render(): string
    {
        $language = Language();
        $html = "";
        $formatInteger = self::$FormatIntegerFunc;
        if ($this->isVisible()) {
            // Do not show record numbers for View/Edit page
            if ($this->PagePhraseId !== "Record") {
                $html .= <<<RECORD
                    <div class="ew-pager ew-rec">
                        <div class="d-inline-flex">
                            <div class="ew-pager-rec me-1">{$language->phrase($this->ItemPhraseId)}</div>
                            <div class="ew-pager-start me-1">{$formatInteger($this->FromIndex)}</div>
                            <div class="ew-pager-to me-1">{$language->phrase("To")}</div>
                            <div class="ew-pager-end me-1">{$formatInteger($this->ToIndex)}</div>
                            <div class="ew-pager-of me-1">{$language->phrase("Of")}</div>
                            <div class="ew-pager-count me-1" data-count="' . $this->RecordCount . '">{$formatInteger($this->RecordCount)}</div>
                        </div>
                    </div>
                    RECORD;
            }
        }
        // Page size selector
        if ($this->UsePageSizeSelector && !empty($this->PageSizes) && !($this->AutoHidePageSizeSelector && $this->RecordCount <= $this->PageSize)) {
            $pageSizes = explode(",", $this->PageSizes);
            $optionsHtml = "";
            foreach ($pageSizes as $pageSize) {
                if (intval($pageSize) > 0) {
                    $optionsHtml .= '<option value="' . $pageSize . '"' . ($this->PageSize == $pageSize ? ' selected' : '') . '>' . $formatInteger($pageSize) . '</option>';
                } else {
                    $optionsHtml .= '<option value="ALL"' . ($this->PageSizeAll ? ' selected' : '') . '>' . $language->phrase("AllRecords") . '</option>';
                }
            };
            $tableRecPerPage = Config("TABLE_REC_PER_PAGE");
            $url = CurrentDashboardPageUrl();
            $useAjax = $this->Table->UseAjaxActions;
            $ajax = $useAjax ? "true" : "false";
            $html .= <<<SELECTOR
                <div class="ew-pager">
                <select name="{$tableRecPerPage}" class="form-select form-select-sm ew-tooltip" title="{$language->phrase("RecordsPerPage")}" data-ew-action="change-page-size" data-ajax="{$ajax}" data-url="{$url}">
                {$optionsHtml}
                </select>
                </div>
                SELECTOR;
        }
        return $html;
    }
}
