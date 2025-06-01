<?php

namespace PHPMaker2025\ucarsip;

use Doctrine\DBAL\Query\QueryBuilder;
use Dflydev\DotAccessData\Data;
use Spatie\Color\Hex;
use Spatie\Color\Rgb;
use Spatie\Color\Rgba;
use Spatie\Color\Hsl;

/**
 * Chart class
 */
class DbChart
{
    protected Language $language;
    public static int $Alpha = 50;
    public static int $BorderAlpha = 100;
    public static int $DefaultAdjust = 10; // Darken/Lighten
    public string $TableVar = ""; // Retained for compatibility
    public string $TableName = ""; // Retained for compatibility
    public int $Position; // Chart Position
    public string $SeriesRenderAs = ""; // Chart Series renderAs
    public string $SeriesYAxis = ""; // Chart Series Y Axis
    public bool $RunTimeSort = false; // Chart run time sort
    public ?int $SortType = 0; // Chart Sort Type
    public string $SortSequence = ""; // Chart Sort Sequence
    public string $ContainerClass = "overlay-wrapper"; // Container class
    public string $DrillDownTable = ""; // Chart drill down table name
    public string $DrillDownUrl = ""; // Chart drill down URL
    public bool $UseDrillDownPanel = false; // Use drill down panel
    public string $DefaultNumberFormat;
    public QueryBuilder $SqlSelect;
    public string $SqlWhere = "";
    public string $SqlGroupBy = "";
    public string $SqlOrderBy = "";
    public string $XAxisDateFormat = "";
    public array $YFieldFormat = [];
    public array $YAxisFormat = [];
    public string $SeriesDateType;
    public QueryBuilder $SqlSelectSeries;
    public string $SqlWhereSeries = "";
    public string $SqlGroupBySeries;
    public string $SqlOrderBySeries;
    public string $ChartSeriesSql = "";
    public string $ChartSql;
    public string $PageBreakType;
    public string $PageBreakClass = ""; // "break-before-page" and/or "break-after-page"
    public bool $DrillDownInPanel = false;
    public bool $ScrollChart = false;
    public bool $IsCustomTemplate = false;
    public ?string $ID = null;
    public ?array $Trends = null;
    public ?array $Data = null;
    public array $ViewData = [];
    public ?array $Series = null;
    public string $Caption = "";
    public string $DataFormat = "json";
    public bool $ScaleBeginWithZero = false;
    public mixed $MinValue = null;
    public mixed $MaxValue = null;
    public bool $ShowPercentage = false; // Pie / Doughnut charts only
    public bool $ShowLookupForXAxis = true;
    public bool $ShowChart = true;
    protected bool $dataLoaded = false;

    // Default border colors in rgb() (see https://github.com/chartjs/Chart.js/blob/master/src/plugins/plugin.colors.ts)
    public static array $DefaultBorderColors = [
        "rgb(54, 162, 235)", // blue
        "rgb(255, 99, 132)", // red
        "rgb(255, 159, 64)", // orange
        "rgb(255, 205, 86)", // yellow
        "rgb(75, 192, 192)", // green
        "rgb(153, 102, 255)", // purple
        "rgb(201, 203, 207)" // grey
    ];

    // Constructor
    public function __construct(
        public DbTableBase $Table,
        public string $ChartVar,
        public readonly string $Name,
        public string $XFieldName,
        public string $YFieldName,
        public string $Type,
        public string $SeriesFieldName,
        public string $SeriesType,
        public string $SummaryType,
        public int $Width,
        public int $Height,
        public string $Align = "",
        public Data $Parameters = new Data()
    ) {
        $this->UseDrillDownPanel = Config("USE_DRILLDOWN_PANEL");
        $this->DefaultNumberFormat = Config("DEFAULT_NUMBER_FORMAT");
        $this->TableVar = $Table->TableVar; // For compatibility
        $this->TableName = $Table->TableName; // For compatibility
        $this->ScaleBeginWithZero = Config("CHART_SCALE_BEGIN_WITH_ZERO");
        if (Config("CHART_SCALE_MINIMUM_VALUE") !== 0) {
            $this->MinValue = Config("CHART_SCALE_MINIMUM_VALUE");
        }
        if (Config("CHART_SCALE_MAXIMUM_VALUE") !== 0) {
            $this->MaxValue = Config("CHART_SCALE_MAXIMUM_VALUE");
        }
        $this->ShowPercentage = Config("CHART_SHOW_PERCENTAGE");
        $this->language = Language();
    }

    // Set chart caption
    public function setCaption(string $v): void
    {
        $this->Caption = $v;
    }

    // Chart caption
    public function caption(): string
    {
        if ($this->Caption != "") {
            return $this->Caption;
        } else {
            return $this->language->chartPhrase($this->Table->TableVar, $this->ChartVar, "ChartCaption");
        }
    }

    // X axis name
    public function xAxisName(): string
    {
        return $this->language->chartPhrase($this->Table->TableVar, $this->ChartVar, "ChartXAxisName");
    }

    // Y axis name
    public function yAxisName(): string
    {
        return $this->language->chartPhrase($this->Table->TableVar, $this->ChartVar, "ChartYAxisName");
    }

    // Primary axis name
    public function primaryYAxisName(): string
    {
        return $this->language->chartPhrase($this->Table->TableVar, $this->ChartVar, "ChartPYAxisName");
    }

    // Function SYAxisName
    public function secondaryYAxisName(): string
    {
        return $this->language->chartPhrase($this->Table->TableVar, $this->ChartVar, "ChartSYAxisName");
    }

    // Sort
    public function getSort(): ?string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->Table->TableVar . "_" . Config("TABLE_SORT_CHART") . "_" . $this->ChartVar));
    }

    public function setSort(string $v): void
    {
        if (Session(AddTabId(PROJECT_NAME . "_" . $this->Table->TableVar . "_" . Config("TABLE_SORT_CHART") . "_" . $this->ChartVar)) != $v) {
            Session(AddTabId(PROJECT_NAME . "_" . $this->Table->TableVar . "_" . Config("TABLE_SORT_CHART") . "_" . $this->ChartVar), $v);
        }
    }

    /**
     * Get chart parameters as array
     *
     * @param string $key Parameter name
     * @return array
     */
    public function getParameters(string $key): array
    {
        return $this->Parameters->has($key) ? $this->Parameters->get($key) : [];
    }

    /**
     * Set chart parameters
     *
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @param bool $output Obsolete. For backward compatibility only.
     * @return void
     */
    public function setParameter(string $key, mixed $value, bool $output = true): void
    {
        $this->Parameters->set($key, $value);
    }

    // Set chart parameters
    public function setParameters(?array $parms): void
    {
        if (is_array($parms)) {
            foreach ($parms as $parm) {
                if (is_array($parm) && count($parm) > 1) {
                    $this->Parameters->set($parm[0], $parm[1]);
                }
            }
        }
    }

    // Set up default chart parameter
    public function setDefaultParameter(string $key, mixed $value): void
    {
        $parm = $this->loadParameter($key);
        if ($parm === null) {
            $this->saveParameter($key, $value);
        }
    }

    // Load chart parameter
    public function loadParameter(string $key): mixed
    {
        return $this->Parameters->has($key) ? $this->Parameters->get($key) : null;
    }

    // Save chart parameter
    public function saveParameter(string $key, mixed $value): void
    {
        $this->Parameters->set($key, $value);
    }

    // Load chart parameters
    public function loadParameters(): void
    {
        // Initialize default values
        $this->setDefaultParameter("caption", "Chart");

        // Show names/values/hover
        $this->setDefaultParameter("shownames", "1"); // Default show names
        $this->setDefaultParameter("showvalues", "1"); // Default show values

        // Get showvalues/showhovercap
        $showValues = (bool)$this->loadParameter("showvalues");
        $showHoverCap = (bool)$this->loadParameter("showhovercap") || (bool)$this->loadParameter("showToolTip");

        // Show tooltip
        if ($showHoverCap && !$this->loadParameter("showToolTip")) {
            $this->saveParameter("showToolTip", "1");
        }

        // Format percent/number for Pie/Doughnut chart
        $showPercentageValues = $this->loadParameter("showPercentageValues");
        $showPercentageInLabel = $this->loadParameter("showPercentageInLabel");
        if ($this->isPieChart() || $this->isDoughnutChart()) {
            if ($showHoverCap == "1" && $showPercentageValues == "1" || $showValues == "1" && $showPercentageInLabel == "1") {
                $this->setDefaultParameter("formatNumber", "1");
                $this->saveParameter("formatNumber", "1");
            }
        }

        // Hide legend for single series (Column/Line/Area 2D)
        if ($this->ScrollChart && $this->isSingleSeries()) {
            $this->setDefaultParameter("showLegend", "0");
            $this->saveParameter("showLegend", "0");
        }
    }

    // Load view data
    public function loadViewData(): void
    {
        $sdt = $this->SeriesDateType;
        $xdt = $this->XAxisDateFormat;
        $ndt = ""; // Not used
        if ($sdt != "") {
            $xdt = $sdt;
        }
        $this->ViewData = [];
        if ($sdt == "" && $xdt == "" && $ndt == "") { // Format Y values
            $cntData = is_array($this->Data) ? count($this->Data) : 0;
            for ($i = 0; $i < $cntData; $i++) {
                $temp = [];
                $chartrow = $this->Data[$i];
                $cntRow = count($chartrow);
                $cntY = $this->SeriesType == 1 && count($this->Series) > 0 ? count($this->Series) : 1;
                for ($j = 0; $j < $cntRow; $j++) {
                    if ($j >= $cntRow - $cntY) {
                        $temp[$j] = $this->formatNumber($chartrow[$j]); // Y values
                    } else {
                        $temp[$j] = $chartrow[$j];
                    }
                }
                $this->ViewData[] = $temp;
            }
        } elseif (is_array($this->Data)) { // Format data
            $cntData = count($this->Data);
            for ($i = 0; $i < $cntData; $i++) {
                $temp = [];
                $chartrow = $this->Data[$i];
                $cntRow = count($chartrow);
                $temp[0] = $this->getXValue($chartrow[0], $xdt); // X value
                $temp[1] = $this->seriesValue($chartrow[1], $sdt); // Series value
                for ($j = 2; $j < $cntRow; $j++) {
                    if ($ndt != "" && $j == $cntRow - 1) {
                        $temp[$j] = $this->getXValue($chartrow[$j], $ndt); // Name value
                    } else {
                        $temp[$j] = $this->formatNumber($chartrow[$j]); // Y values
                    }
                }
                $this->ViewData[] = $temp;
            }
        }
    }

    // Set up chart
    public function setupChart(): void
    {
        global $DashboardReport, $ExportType, $Page;

        // Set up chart base SQL
        if ($this->Table->TableReportType == "crosstab") { // Crosstab chart
            $sqlSelect = $this->Table->getSqlSelect()->addSelect($this->Table->DistinctColumnFields);
            $sqlChartSelect = $this->SqlSelect;
        } else {
            $sqlSelect = $this->Table->getSqlSelect();
            $sqlChartSelect = $this->SqlSelect;
        }
        $pageFilter = $Page?->Filter ?? "";
        if ($this->Table->TableType == "REPORT") { // Page_Selecting for reports
            if (method_exists($Page, "pageSelecting")) {
                $Page->pageSelecting($pageFilter);
            }
        } else { // Records_Selecting for tables
            if (method_exists($Page, "recordsSelecting")) {
                $Page->recordsSelecting($pageFilter);
            }
        }
        $dbType = GetConnectionType($this->Table->Dbid);
        if ($this->Table->SourceTableIsCustomView) {
            $sqlChartBase = "(" . $this->buildReportSql($sqlSelect, $this->Table->getSqlFrom(), $this->Table->getSqlWhere(), $this->Table->getSqlGroupBy(), $this->Table->getSqlHaving(), ($dbType == "MSSQL") ? $this->Table->getSqlOrderBy() : "", $pageFilter, "")->getSQL() . ") TMP_TABLE";
        } else {
            $sqlChartBase = $this->Table->getSqlFrom();
        }

        // Set up chart series
        if (!IsEmpty($this->SeriesFieldName)) {
            if ($this->SeriesType == 1) { // Multiple Y fields
                $ar = explode("|", $this->SeriesFieldName);
                $cnt = count($ar);
                $yaxis = explode(",", $this->SeriesYAxis);
                for ($i = 0; $i < $cnt; $i++) {
                    $fld = &$this->Table->Fields[$ar[$i]];
                    if (StartsString("4", strval($this->Type))) { // Combination charts
                        $series = @$yaxis[$i] == "2" ? "y1" : "y";
                        $this->Series[] = [$fld->caption(), $series];
                    } else {
                        $this->Series[] = $fld->caption();
                    }
                }
            } elseif ($this->Table->TableReportType == "crosstab" && $this->SeriesFieldName == $this->Table->ColumnFieldName && $this->Table->ColumnDateSelection && $this->Table->ColumnDateType == "q") { // Quarter
                for ($i = 1; $i <= 4; $i++) {
                    $this->Series[] = QuarterName($i);
                }
            } elseif ($this->Table->TableReportType == "crosstab" && $this->SeriesFieldName == $this->Table->ColumnFieldName && $this->Table->ColumnDateSelection && $this->Table->ColumnDateType == "m") { // Month
                for ($i = 1; $i <= 12; $i++) {
                    $this->Series[] = MonthName($i);
                }
            } else { // Load chart series from SQL directly
                if ($this->Table->SourceTableIsCustomView) {
                    $sql = $this->buildReportSql($this->SqlSelectSeries, $sqlChartBase, $this->SqlWhereSeries, $this->SqlGroupBySeries, "", $this->SqlOrderBySeries, "", "");
                } else {
                    $chartFilter = $this->SqlWhereSeries;
                    AddFilter($chartFilter, $this->Table->getSqlWhere());
                    $sql = $this->buildReportSql($this->SqlSelectSeries, $sqlChartBase, $chartFilter, $this->SqlGroupBySeries, "", $this->SqlOrderBySeries, $pageFilter, "");
                }
                $this->ChartSeriesSql = $sql->getSQL();
            }
        }

        // Run time sort, update SqlOrderBy
        if ($this->RunTimeSort) {
            $this->SqlOrderBy .= ($this->SortType == 2) ? " DESC" : "";
        }

        // Set up ChartSql
        if ($this->Table->SourceTableIsCustomView) {
            $sql = $this->buildReportSql($sqlChartSelect, $sqlChartBase, $this->SqlWhere, $this->SqlGroupBy, "", $this->SqlOrderBy, "", "");
        } else {
            $chartFilter = $this->SqlWhere;
            AddFilter($chartFilter, $this->Table->getSqlWhere());
            $sql = $this->buildReportSql($sqlChartSelect, $sqlChartBase, $chartFilter, $this->SqlGroupBy, "", $this->SqlOrderBy, $pageFilter, "");
        }
        $this->ChartSql = $sql->getSQL();
    }

    // Load chart data
    public function loadChartData(): void
    {
        // Data already loaded, return
        if ($this->dataLoaded) {
            return;
        }

        // Setup chart series data
        if ($this->ChartSeriesSql != "") {
            $this->loadSeries();
            if (IsDebug()) {
                LogInfo("(Chart Series SQL): " . $this->ChartSeriesSql);
            }
        }

        // Setup chart data
        if ($this->ChartSql != "") {
            $this->loadData();
            if (IsDebug()) {
                LogInfo("(Chart SQL): " . $this->ChartSql);
            }
        }

        // Sort data
        if ($this->SeriesFieldName != "" && $this->SeriesType != 1) {
            $this->sortMultiData();
        } else {
            $this->sortData();
        }
        $this->dataLoaded = true;
    }

    // Load Chart Series
    public function loadSeries(): void
    {
        $sql = $this->ChartSeriesSql;
        $cnn = Conn($this->Table->Dbid);
        $sdt = $this->SeriesDateType;
        $rows = $cnn->executeQuery($sql)->fetchAllNumeric();
        foreach ($rows as $row) {
            $this->Series[] = $this->seriesValue($row[0], $sdt); // Series value
        }
    }

    // Get Chart Series value
    public function seriesValue(mixed $val, string $dt): ?string
    {
        if ($val === null) {
            return $val;
        }
        if ($dt == "syq") {
            $ar = explode("|", strval($val));
            if (count($ar) >= 2) {
                return $ar[0] . " " . QuarterName((int)$ar[1]);
            } else {
                return strval($val);
            }
        } elseif ($dt == "sym") {
            $ar = explode("|", strval($val));
            if (count($ar) >= 2) {
                return $ar[0] . " " . MonthName((int)$ar[1]);
            } else {
                return strval($val);
            }
        } elseif ($dt == "sq") {
            return QuarterName((int)$val);
        } elseif ($dt == "sm") {
            return MonthName((int)$val);
        } else {
            if (is_string($val)) {
                return trim($val);
            } else {
                return strval($val);
            }
        }
    }

    // Load Chart Data from SQL
    public function loadData(): void
    {
        $sql = $this->ChartSql;
        $cnn = Conn($this->Table->Dbid);
        $rows = $cnn->executeQuery($sql)->fetchAllNumeric();
        foreach ($rows as $row) {
            if ($this->ShowLookupForXAxis) {
                $row = $this->Table->renderChartXAxis($this->ChartVar, $row);
            }
            $this->Data[] = $row;
        }
    }

    // Get Chart X value
    public function getXValue(mixed $val, string $dt): string
    {
        if ($val === null) {
            return $val;
        }
        if (is_numeric($dt)) {
            return FormatDateTime($val, $dt);
        } elseif ($dt == "y") {
            return strval($val);
        } elseif ($dt == "xyq") {
            $ar = explode("|", strval($val));
            if (count($ar) >= 2) {
                return $ar[0] . " " . QuarterName((int)$ar[1]);
            } else {
                return strval($val);
            }
        } elseif ($dt == "xym") {
            $ar = explode("|", strval($val));
            if (count($ar) >= 2) {
                return $ar[0] . " " . MonthName((int)$ar[1]);
            } else {
                return strval($val);
            }
        } elseif ($dt == "xq") {
            return QuarterName((int)$val);
        } elseif ($dt == "xm") {
            return MonthName((int)$val);
        } else {
            if (is_string($val)) {
                return trim($val);
            } else {
                return strval($val);
            }
        }
    }

    // Sort chart data
    public function sortData(): void
    {
        $ar = &$this->Data;
        $opt = intval($this->SortType);
        $seq = $this->SortSequence;
        if (($opt < 3 || $opt > 4) && $seq == "" || ($opt < 1 || $opt > 4) && $seq != "") {
            return;
        }
        if (is_array($ar)) {
            $cntar = count($ar);
            for ($i = 0; $i < $cntar; $i++) {
                for ($j = $i + 1; $j < $cntar; $j++) {
                    $swap = match ($opt) {
                        1 => CompareValueCustom($ar[$i][0], $ar[$j][0], $seq), // X values ascending
                        2 => CompareValueCustom($ar[$j][0], $ar[$i][0], $seq), // X values descending
                        3 => CompareValueCustom($ar[$i][2], $ar[$j][2], $seq), // Y values ascending
                        4 => CompareValueCustom($ar[$j][2], $ar[$i][2], $seq) // Y values descending
                    };
                    if ($swap) {
                        $tmpar = $ar[$i];
                        $ar[$i] = $ar[$j];
                        $ar[$j] = $tmpar;
                    }
                }
            }
        }
    }

    // Sort chart multi series data
    public function sortMultiData(): void
    {
        $ar = &$this->Data;
        $opt = $this->SortType;
        $seq = $this->SortSequence;
        if (!is_array($ar) || ($opt < 3 || $opt > 4) && $seq == "" || ($opt < 1 || $opt > 4) && $seq != "") {
            return;
        }

        // Obtain a list of columns
        foreach ($ar as $key => $row) {
            $xvalues[$key] = $row[0];
            $series[$key] = $row[1];
            $yvalues[$key] = $row[2];
            $ysums[$key] = $row[0]; // Store the x-value for the time being
            if (isset($xsums[$row[0]])) {
                $xsums[$row[0]] += $row[2];
            } else {
                $xsums[$row[0]] = $row[2];
            }
        }

        // Set up Y sum
        if ($opt == 3 || $opt == 4) {
            $cnt = count($ysums);
            for ($i = 0; $i < $cnt; $i++) {
                $ysums[$i] = $xsums[$ysums[$i]];
            }
        }

        // No specific sequence, use array_multisort
        if ($seq == "") {
            match ($opt) {
                1 => array_multisort($xvalues, SORT_ASC, $ar), // X values ascending
                2 => array_multisort($xvalues, SORT_DESC, $ar), // X values descending
                3 => array_multisort($ysums, SORT_ASC, $ar), // Y values ascending
                4 => array_multisort($ysums, SORT_DESC, $ar) // Y values descending
            };
        // Handle specific sequence
        } else {
            // Build key list
            if ($opt == 1 || $opt == 2) {
                $vals = array_unique($xvalues);
            } else {
                $vals = array_unique($ysums);
            }
            foreach ($vals as $key => $val) {
                $keys[] = [$key, $val];
            }

            // Sort key list based on specific sequence
            $cntkey = count($keys);
            for ($i = 0; $i < $cntkey; $i++) {
                for ($j = $i + 1; $j < $cntkey; $j++) {
                    $swap = match ($opt) {
                        1, 3 => CompareValueCustom($keys[$i][1], $keys[$j][1], $seq), // Ascending
                        2, 4 => CompareValueCustom($keys[$j][1], $keys[$i][1], $seq) // Descending
                    };
                    if ($swap) {
                        $tmpkey = $keys[$i];
                        $keys[$i] = $keys[$j];
                        $keys[$j] = $tmpkey;
                    }
                }
            }
            for ($i = 0; $i < $cntkey; $i++) {
                $xsorted[] = $xvalues[$keys[$i][0]];
            }

            // Sort array based on x sequence
            $arwrk = $ar;
            $rowcnt = 0;
            $cntx = intval(count($xsorted));
            for ($i = 0; $i < $cntx; $i++) {
                foreach ($arwrk as $key => $row) {
                    if ($row[0] == $xsorted[$i]) {
                        $ar[$rowcnt] = $row;
                        $rowcnt++;
                    }
                }
            }
        }
    }

    // Get default alpha
    public static function getDefaultAlpha(): int
    {
        return self::$Alpha;
    }

    // Get background opacity
    public function getOpacity(?int $alpha, ?int $def = null): ?float
    {
        if ($alpha !== null) {
            $alpha = (int)$alpha;
            if ($alpha > 100) {
                $alpha = 100;
            } elseif ($alpha <= 0) {
                $alpha = $def ?? 0; // Use default
            }
            return (float)$alpha / 100;
        }
        return null;
    }

    // Adjust lightness (Darken/Lighten) a HSL value
    private function adjustLightness(Hsl $hsl, ?int $amount = null): Hsl
    {
        $amount ??= self::$DefaultAdjust;
        $lightness = $hsl->lightness();
        $lightness = $lightness + $amount;
        $lightness = ($lightness < 0) ? 0 : $lightness;
        $lightness = ($lightness > 100) ? 100 : $lightness;
        return new Hsl($hsl->hue(), $hsl->saturation(), $lightness);
    }

    // Get Rgba color
    public function getRgbaColor(string $color, ?float $opacity = null)
    {
        // Check opacity
        if ($opacity === null) {
            return $color;
        } elseif (!is_float($opacity)) {
            $opacity = (float)$opacity;
        } elseif ($opacity > 1) {
            $opacity = 1.0;
        } elseif ($opacity < 0) {
            $opacity = 0.0;
        }

        // Convert color
        if (str_starts_with($color, "#")) {
            return (string) Hex::fromString($color)->toRgba($opacity);
        } elseif (str_starts_with($color, "rgb(")) {
            return (string) Rgb::fromString($color)->toRgba($opacity);
        } elseif (str_starts_with($color, "rgba(")) {
            return (string) Rgba::fromString($color);
        }
        return $color;
    }

    // Get Rgb color
    public function getRgbColor(string $color): string
    {
        if (preg_match('/^(?:[a-f0-9]{3}|[a-f0-9]{4}|[a-f0-9]{6}|[a-f0-9]{8})$/i', $color)) { // rgb / rgba / rrggbb / rrggbbaa (without #)
            $color = "#" . $color;
        }
        if (str_starts_with($color, "#")) {
            return (string) Hex::fromString($color)->toRgb();
        }
        return $color;
    }

    // Get color
    public function getColor(int $i): string
    {
        $colors = $this->loadParameter("colorpalette") ?: Config("CHART_COLOR_PALETTE");
        $colors = trim($colors);
        $colors = $colors ? array_map(fn($c) => $this->getRgbColor(trim($c)), preg_split('/[|,]/', $colors)) : self::$DefaultBorderColors;
        $count = count($colors);
        $color = $colors[$i % $count];
        $q = intdiv($i, $count) % 3;
        $factor = $q < 2 ? $q : -1;
        if ($factor != 0) { // Get color variant
            $rgb = Rgb::fromString($color);
            $color = $this->adjustLightness($rgb->toHsl(), $factor * self::$DefaultAdjust)->toRgb();
        }
        return (string) $color;
    }

    // Get RGBA background color
    public function getRgbaBackgroundColor(int $i, ?float $opacity = null): string
    {
        $color = $this->getColor($i);
        $opacity ??= $this->getOpacity($this->loadParameter("alpha"), self::getDefaultAlpha());
        return $this->getRgbaColor($color, $opacity);
    }

    // Get RGBA background color
    public function getRgbaBorderColor(int $i, ?float $opacity = null): string
    {
        $color = $this->getColor($i);
        $opacity ??= $this->getOpacity(self::$BorderAlpha);
        return $this->getRgbaColor($color, $opacity);
    }

    // Format name for chart
    public function formatName(?string $name): string
    {
        if ($name === null) {
            return $this->language->phrase("NullLabel");
        } elseif ($name == "") {
            return $this->language->phrase("EmptyLabel");
        }
        return $name;
    }

    // Is single series chart
    public function isSingleSeries(): bool
    {
        return StartsString("1", strval($this->Type));
    }

    // Is zoom line chart
    public function isZoomLineChart(): bool
    {
        return EndsString("92", strval($this->Type));
    }

    // Is column chart
    public function isColumnChart(): bool
    {
        return EndsString("01", strval($this->Type));
    }

    // Is line chart
    public function isLineChart(): bool
    {
        return EndsString("02", strval($this->Type));
    }

    // Is area chart
    public function isAreaChart(): bool
    {
        return EndsString("03", strval($this->Type));
    }

    // Is bar chart
    public function isBarChart(): bool
    {
        return EndsString("04", strval($this->Type));
    }

    // Is pie chart
    public function isPieChart(): bool
    {
        return EndsString("05", strval($this->Type));
    }

    // Is doughnut chart
    public function isDoughnutChart(): bool
    {
        return EndsString("06", strval($this->Type));
    }

    // Is polar area chart
    public function isPolarAreaChart(): bool
    {
        return EndsString("07", strval($this->Type));
    }

    // Is radar chart
    public function isRadarChart(): bool
    {
        return EndsString("08", strval($this->Type));
    }

    // Is stack chart
    public function isStackedChart(): bool
    {
        return StartsString("3", strval($this->Type)) || in_array(strval($this->Type), ["4021", "4121", "4141"]);
    }

    // Is combination chart
    public function isCombinationChart(): bool
    {
        return StartsString("4", strval($this->Type));
    }

    // Is dual axis chart
    public function isDualAxisChart(): bool
    {
        return in_array(strval($this->Type), ["4031", "4131", "4141"]);
    }

    // Format number for chart
    public function formatNumber(?float $v): ?string
    {
        if ($v === null)
            return $v;
        if ($this->DefaultNumberFormat) {
            $fmt = new \NumberFormatter("en-US", \NumberFormatter::PATTERN_DECIMAL, $this->DefaultNumberFormat);
            $fmt->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, ""); // Always format without thousand separator
            return $fmt->format($v);
        } else {
            $decimals = (($v - (int)$v) == 0) ? 0 : strlen(abs($v - (int)$v)) - 2; // Use original decimal precision
            return number_format($v, $decimals, ".", "");
        }
    }

    // Get chart X SQL
    public function getXSql(string $fldsql, DataType $fldtype, mixed $val, string $dt): string
    {
        if ($val === null) {
            return $fldsql . " IS NULL";
        }
        $dbid = $this->Table->Dbid;
        if (is_numeric($dt)) {
            return $fldsql . " = " . QuotedValue(UnformatDateTime($val, $dt), $fldtype, $dbid);
        } elseif ($dt == "y") {
            if (is_numeric($val)) {
                return GroupSql($fldsql, "y", 0, $dbid) . " = " . QuotedValue($val, DataType::NUMBER, $dbid);
            } else {
                return $fldsql . " = " . QuotedValue($val, $fldtype, $dbid);
            }
        } elseif ($dt == "xyq") {
            $ar = explode("|", strval($val));
            if (count($ar) >= 2 && is_numeric($ar[0]) && is_numeric($ar[1])) {
                return GroupSql($fldsql, "y", 0, $dbid) . " = " . QuotedValue($ar[0], DataType::NUMBER, $dbid) . " AND " . GroupSql($fldsql, "xq", 0, $dbid) . " = " . QuotedValue($ar[1], DataType::NUMBER, $dbid);
            } else {
                return $fldsql . " = " . QuotedValue($val, $fldtype, $dbid);
            }
        } elseif ($dt == "xym") {
            $ar = explode("|", strval($val));
            if (count($ar) >= 2 && is_numeric($ar[0]) && is_numeric($ar[1])) {
                return GroupSql($fldsql, "y", 0, $dbid) . " = " . QuotedValue($ar[0], DataType::NUMBER, $dbid) . " AND " . GroupSql($fldsql, "xm", 0, $dbid) . " = " . QuotedValue($ar[1], DataType::NUMBER, $dbid);
            } else {
                return $fldsql . " = " . QuotedValue($val, $fldtype, $dbid);
            }
        } elseif ($dt == "xq") {
            return GroupSql($fldsql, "xq", 0, $dbid) . " = " . QuotedValue($val, DataType::NUMBER, $dbid);
        } elseif ($dt == "xm") {
            return GroupSql($fldsql, "xm", 0, $dbid) . " = " . QuotedValue($val, DataType::NUMBER, $dbid);
        } else {
            return $fldsql . " = " . QuotedValue($val, $fldtype, $dbid);
        }
    }

    // Get chart series SQL
    public function getSeriesSql(string $fldsql, DataType $fldtype, mixed $val, string $dt): string
    {
        if ($val === null) {
            return $fldsql . " IS NULL";
        }
        $dbid = $this->Table->Dbid;
        if ($dt == "y") {
            if (is_numeric($val)) {
                return GroupSql($fldsql, "y", 0, $dbid) . " = " . QuotedValue($val, DataType::NUMBER, $dbid);
            } else {
                return $fldsql . " = " . QuotedValue($val, $fldtype, $dbid);
            }
        } elseif ($dt == "syq") {
            $ar = explode("|", strval($val));
            if (count($ar) >= 2 && is_numeric($ar[0]) && is_numeric($ar[1])) {
                return GroupSql($fldsql, "y", 0, $dbid) . " = " . QuotedValue($ar[0], DataType::NUMBER, $dbid) . " AND " . GroupSql($fldsql, "xq", 0, $dbid) . " = " . QuotedValue($ar[1], DataType::NUMBER, $dbid);
            } else {
                return $fldsql . " = " . QuotedValue($val, $fldtype, $dbid);
            }
        } elseif ($dt == "sym") {
            $ar = explode("|", strval($val));
            if (count($ar) >= 2 && is_numeric($ar[0]) && is_numeric($ar[1])) {
                return GroupSql($fldsql, "y", 0, $dbid) . " = " . QuotedValue($ar[0], DataType::NUMBER, $dbid) . " AND " . GroupSql($fldsql, "xm", 0, $dbid) . " = " . QuotedValue($ar[1], DataType::NUMBER, $dbid);
            } else {
                return $fldsql . " = " . QuotedValue($val, $fldtype, $dbid);
            }
        } elseif ($dt == "sq") {
            return GroupSql($fldsql, "xq", 0, $dbid) . " = " . QuotedValue($val, DataType::NUMBER, $dbid);
        } elseif ($dt == "sm") {
            return GroupSql($fldsql, "xm", 0, $dbid) . " = " . QuotedValue($val, DataType::NUMBER, $dbid);
        } else {
            return $fldsql . " = " . QuotedValue($val, $fldtype, $dbid);
        }
    }

    // Get renderAs
    public function getRenderAs(int $i): string
    {
        $ar = explode(",", $this->SeriesRenderAs);
        return ($i < count($ar)) ? $ar[$i] : "";
    }

    // Has data
    public function hasData(): bool
    {
        return is_array($this->Data) && count($this->Data) > 0;
    }

    // Render chart
    public function render(string $class = "", ?int $width = null, ?int $height = null): string
    {
        global $ExportType, $Page, $DashboardReport;

        // Skip if ShowChart disabled
        if (!$this->ShowChart) {
            return "";
        }

        // Skip if isAddOrEdit
        if ($Page != null && method_exists($Page, "isAddOrEdit") && $Page->isAddOrEdit()) {
            return "";
        }

        // Get renderer class
        $rendererClass = ChartTypes::getRendererClass($this->Type);

        // Check chart size
        $width ??= $this->Width ?: $rendererClass::$DefaultWidth;
        $height ??= $this->Height ?: $rendererClass::$DefaultHeight;

        // Set up chart
        $this->setupChart();

        // Output HTML
        AppendClass($class, $this->ContainerClass); // Add container class
        $html = '<div class="' . $class . '" data-chart="' . $this->ID . '">'; // Start chart

        // Load chart data
        $this->loadChartData();
        $this->loadParameters();
        $this->loadViewData();

        // Disable animation if export
        if (!IsEmpty($ExportType)) {
            $this->setParameters([
                ["options.animation", false], // Disables all animations
                ["options.animations.colors", false], // Disables animation defined by the collection of 'colors' properties
                ["options.animations.x", false], // Disables animation defined by the 'x' property
                ["options.transitions.active.animation.duration", 0] // Disables the animation for 'active' mode
            ]);
        }

        // Get renderer
        $renderer = Container()->make($rendererClass, ["Chart" => $this]);

        // Output chart HTML first
        $isDrillDown = $Page?->DrillDown ?? false;
        $html .= '<a id="cht_' . $this->ID . '"></a>' . // Anchor
            '<div id="div_cht_' . $this->ID . '" class="ew-chart' . (!$DashboardReport && $this->PageBreakClass ? ' ' . $this->PageBreakClass : '') . '">';
        if ($this->RunTimeSort && !$isDrillDown && $ExportType == "" && $this->hasData()) {
            $url = GetUrl($this->Table->getDefaultRouteUrl() . '/' . $this->ChartVar);
            if ($DashboardReport) {
                $url .= "?" . Config("PAGE_DASHBOARD") . "=" . $DashboardReport;
            }
            $html .= '<form class="row mb-3 ew-chart-sort" action="' . $url . '"><div class="col-sm-auto">' .
                $this->language->phrase("ChartOrder") .
                '</div><div class="col-sm-auto"><select id="chartordertype" name="chartordertype" class="form-select" data-ew-action="chart-order">' .
                '<option value="1"' . ($this->SortType == '1' ? ' selected' : '') . '>' . $this->language->phrase("ChartOrderXAsc") . '</option>' .
                '<option value="2"' . ($this->SortType == '2' ? ' selected' : '') . '>' . $this->language->phrase("ChartOrderXDesc") . '</option>' .
                '<option value="3"' . ($this->SortType == "3" ? ' selected' : '') . '>' . $this->language->phrase("ChartOrderYAsc") . '</option>' .
                '<option value="4"' . ($this->SortType == "4" ? ' selected' : '') . '>' . $this->language->phrase("ChartOrderYDesc") . '</option>' .
                '</select>' .
                ($DashboardReport ? '<input type="hidden" id="' . Config("PAGE_DASHBOARD") . '" name="' . Config("PAGE_DASHBOARD") . '" value="' . $DashboardReport . '">' : '') .
                '<input type="hidden" id="width" name="width" value="' . $width . '">' .
                '<input type="hidden" id="height" name="height" value="' . $height . '">' .
                '</div></form>';
        }
        $html .= $renderer->getContainer($width, $height) .
            '</div>' .
            $renderer->getScript($width, $height); // JavaScript
        $html .= '</div>'; // End chart
        return $html;
    }

    /**
     * Build Report SQL
     *
     * @param string|QueryBuilder $select
     * @param string $from
     * @param string $where
     * @param string $groupBy
     * @param string $having
     * @param string $orderBy
     * @param string $filter
     * @param string $sort
     * @return QueryBuilder
     */
    public function buildReportSql(string|QueryBuilder $select, string $from, string $where, string $groupBy, string $having, string $orderBy, string $filter, string $sort): QueryBuilder
    {
        if (is_string($select)) {
            $queryBuilder = $this->getQueryBuilder();
            $queryBuilder->select($select);
        } elseif ($select instanceof QueryBuilder) {
            $queryBuilder = $select;
        }
        if ($from != "") {
            $queryBuilder->from($from);
        }
        if ($where != "") {
            $queryBuilder->where($where);
        }
        if ($filter != "") {
            $queryBuilder->andWhere($filter);
        }
        if ($groupBy != "") {
            $queryBuilder->groupBy($groupBy);
        }
        if ($having != "") {
            $queryBuilder->having($having);
        }
        $flds = UpdateSortFields($orderBy, $sort, 1);
        if (is_array($flds)) {
            foreach ($flds as $fld) {
                $queryBuilder->addOrderBy($fld[0], $fld[1]);
            }
        }
        return $queryBuilder;
    }

    // Chart Rendered event
    public function chartRendered(ChartRendererInterface $renderer): void
    {
        // Example:
        // $chartData = &$renderer->Data;
        // $chartOptions = &$renderer->Options;
        // var_dump($this->ID, $chartData, $chartOptions); // View chart ID, data and options
        // if ($this->ID == "<Table>_<Chart>") { // Check chart ID
        // Your code to customize $chartData and/or $chartOptions;
        // }
    }
}
