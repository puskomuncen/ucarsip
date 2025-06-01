<?php

namespace PHPMaker2025\ucarsip;

/**
 * Class for crosstab
 */
class CrosstabTable extends ReportTable
{
    // Column field related
    public string $ColumnFieldName = "";
    public bool $ColumnDateSelection = false;
    public string $ColumnDateType = "";

    // Summary fields
    public array $SummaryFields = [];
    public string $SummarySeparatorStyle = "unstyled";

    // Summary cells
    public array $SummaryCellAttrs = [];
    public array $SummaryViewAttrs = [];
    public array $SummaryLinkAttrs = [];
    public array $SummaryCurrentValues = [];
    public array $SummaryViewValues = [];
    public int $CurrentIndex = -1;

    // Constructor
    public function __construct(Language $language, AdvancedSecurity $security)
    {
        parent::__construct($language, $security);
    }

    // Summary cell attributes
    public function summaryCellAttributes(int $i): string
    {
        if (is_array($this->SummaryCellAttrs)) {
            if ($i >= 0 && $i < count($this->SummaryCellAttrs)) {
                $attrs = $this->SummaryCellAttrs[$i];
                if (is_array($attrs)) {
                    return Attributes::create($attrs)->toString();
                }
            }
        }
        return "";
    }

    // Summary view attributes
    public function summaryViewAttributes(int $i): string
    {
        if (is_array($this->SummaryViewAttrs)) {
            if ($i >= 0 && $i < count($this->SummaryViewAttrs)) {
                $attrs = $this->SummaryViewAttrs[$i];
                if (is_array($attrs)) {
                    return Attributes::create($attrs)->toString();
                }
            }
        }
        return "";
    }

    // Summary link attributes
    public function summaryLinkAttributes(int $i): string
    {
        if (is_array($this->SummaryLinkAttrs)) {
            if ($i >= 0 && $i < count($this->SummaryLinkAttrs)) {
                $attrs = $this->SummaryLinkAttrs[$i];
                if (is_array($attrs)) {
                    return Attributes::create($attrs)->toString();
                }
            }
        }
        return "";
    }

    // Render summary fields
    public function renderSummaryFields(int $idx): string
    {
        global $ExportType;
        $html = "";
        $cnt = count($this->SummaryFields);
        for ($i = 0; $i < $cnt; $i++) {
            $smry = &$this->SummaryFields[$i];
            $vv = $smry->SummaryViewValues[$idx];
            $attrs = $smry->SummaryLinkAttrs[$idx] ?? [];
            if ($attrs["href"] ?? $attrs["data-ew-action"] ?? false) {
                $vv = "<a" . $smry->summaryLinkAttributes($idx) . ">" . $vv . "</a>";
            }
            $vv = "<span" . $smry->summaryViewAttributes($idx) . ">" . $vv . "</span>";
            if ($cnt > 0) {
                if ($ExportType == "" || $ExportType == "print") {
                    $vv = "<li class=\"ew-value-" . strtolower($smry->SummaryType) . "\">" . $vv . "</li>";
                } elseif ($ExportType == "excel" && Config("USE_PHPEXCEL") || $ExportType == "word" && Config("USE_PHPWORD")) {
                    $vv .= "    ";
                } else {
                    $vv .= "<br>";
                }
            }
            $html .= $vv;
        }
        if ($cnt > 0 && ($ExportType == "" || $ExportType == "print")) {
            $html = "<ul class=\"list-" . $this->SummarySeparatorStyle . " ew-crosstab-values\">" . $html . "</ul>";
        }
        return $html;
    }

    // Render summary types
    public function renderSummaryCaptions(string $typ = ""): string
    {
        global $ExportType;
        $html = "";
        $cnt = count($this->SummaryFields);
        if ($typ == "page") {
            return $this->language->phrase("RptPageSummary");
        } elseif ($typ == "grand") {
            return $this->language->phrase("RptGrandSummary");
        } else {
            for ($i = 0; $i < $cnt; $i++) {
                $smry = &$this->SummaryFields[$i];
                $smryCaption = $smry->SummaryCaption;
                $fld = $this->Fields[$smry->Name];
                $caption = $fld->caption();
                if ($caption != "") {
                    $content = $caption . '&nbsp;<span class="ew-aggregate-caption">(' . $smryCaption . ')</span>';
                }
                if ($cnt > 0) {
                    if ($ExportType == "" || $ExportType == "print") {
                        $content = "<li>" . $content . "</li>";
                    } elseif ($ExportType == "excel" && Config("USE_PHPEXCEL") || $ExportType == "word" && Config("USE_PHPWORD")) {
                        $content .= "    ";
                    } else {
                        $content .= "<br>";
                    }
                }
                $html .= $content;
            }
            if ($cnt > 0 && ($ExportType == "" || $ExportType == "print")) {
                $html = "<ul class=\"list-" . $this->SummarySeparatorStyle . " ew-crosstab-values\">" . $html . "</ul>";
            }
            return $html;
        }
    }
}
