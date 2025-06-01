<?php

namespace PHPMaker2025\ucarsip;

/**
 * Pager item class
 */
class PagerItem
{
    /**
     * Constructor
     *
     * @param string $contextClass Context class
     * @param int $pageSize Page size
     * @param int $start Record number (1-based)
     * @param string $text Text
     * @param bool $enabled Enabled
     * @return void
     */
    public function __construct(
        public string $ContextClass,
        public int $PageSize,
        public int $Start = 1,
        public string $Text = "",
        public bool $Enabled = false
    ) {
    }

    /**
     * Get page number
     *
     * @return int
     */
    public function getPageNumber(): int
    {
        return ($this->PageSize > 0 && $this->Start > 0) ? ceil($this->Start / $this->PageSize) : 1;
    }

    /**
     * Get URL or query string
     *
     * @param ?string $url URL without query string
     * @return string
     */
    public function getUrl(?string $url = null): string
    {
        global $DashboardReport;
        $qs = Config("TABLE_PAGE_NUMBER") . "=" . $this->getPageNumber();
        if ($DashboardReport) {
            $qs .= "&" . Config("PAGE_DASHBOARD") . "=" . $DashboardReport;
        }
        return $url ? UrlAddQuery($url, $qs) : $qs;
    }

    /**
     * Get "disabled" class
     *
     * @return string
     */
    public function getDisabledClass(): string
    {
        return $this->Enabled ? "" : " disabled";
    }

    /**
     * Get "active" class
     *
     * @return string
     */
    public function getActiveClass(): string
    {
        return $this->Enabled ? "" : " active";
    }

    /**
     * Get attributes
     * - data-ew-action and data-url for normal List pages
     * - data-page for other pages
     *
     * @param ?string $url URL without query string
     * @param string $action Action (redirect/refresh)
     * @return string
     */
    public function getAttributes(?string $url = "", string $action = "redirect"): string
    {
        return 'data-ew-action="' . ($this->Enabled ? $action : "none") . '" data-url="' . $this->getUrl($url) . '" data-page="' . $this->getPageNumber() . '"' .
            ($this->ContextClass ? ' data-context="' . HtmlEncode($this->ContextClass) . '"' : "");
    }
}
