<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpForbiddenException;
use DiDom\Document;
use DiDom\Element;
use InvalidArgumentException;
use Exception;
use Throwable;
use ZipArchive;

/**
 * Export Handler class
 */
class ExportHandler
{

    public function __construct(
        protected Request $request,
        protected Response &$response,
        protected Language $language,
        protected AdvancedSecurity $security,
        protected ContainerInterface $container,
    ) {
    }

    /**
     * Export data
     * - api/export/guid
     * - api/export/search
     * - api/export/type/object
     *
     * @return bool Whether data is exported successfully
     */
    public function __Invoke(): Response
    {
        // Get parameters
        $exportType = strtolower(Get(Config("API_EXPORT_NAME"), Route("param")) ?? "");
        $table = Get(Config("API_OBJECT_NAME"), Route("table"));
        $recordKey = Get(Config("API_KEY_NAME"));
        try {
            if ($table === null) {
                if (Route("param") === Config("EXPORT_LOG_SEARCH")) { // Search
                    $output = ConvertToBool(Get(Config("API_EXPORT_OUTPUT"), true)); // Output by default unless output=0
                    $this->searchExportLog($output);
                } else {
                    $this->writeExportFile($exportType, Get(Config("API_EXPORT_FILE_NAME")));
                }
            } else {
                $save = ConvertToBool(Get(Config("API_EXPORT_SAVE"))) && !IsEmpty(Config("EXPORT_LOG_TABLE_VAR"));
                $output = ConvertToBool(Get(Config("API_EXPORT_OUTPUT"), true)) || !$save; // Output by default unless output=0 and not save
                $this->exportData($exportType, $table, $recordKey, $output, $save);
            }
        } catch (HttpForbiddenException $e) {
            $this->response = $this->response->withStatus(403)
                ->withJson(["success" => false, "error" => Language()->phrase("403Desc")]);
        }
        return $this->response;
    }

    // Search export log
    protected function searchExportLog(bool $output)
    {
        $zipNames = [];
        $zipNames[] = Config("EXPORT_LOG_ARCHIVE_PREFIX");
        $exportLogTable = Config("EXPORT_LOG_TABLE_VAR");
        if (IsEmpty($exportLogTable)) {
            throw new InvalidArgumentException("Missing export log table variable");
        }
        $tbl = $this->container->get($exportLogTable);
        $this->security->loadTablePermissions($exportLogTable);
        if (!$this->security->canList()) {
            throw new HttpForbiddenException($this->request);
        }
        $filter = $tbl->applyUserIDFilters("");
        // Handle export type
        $fld = $tbl->Fields[Config("EXPORT_LOG_FIELD_NAME_EXPORT_TYPE")];
        $fld->AdvancedSearch->parseSearchValue(Get(Config("EXPORT_LOG_FIELD_NAME_EXPORT_TYPE_ALIAS")));
        $exportType = $fld->AdvancedSearch->SearchValue;
        if (!IsEmpty($exportType)) {
            $zipNames[] = $exportType;
            $opr = $fld->AdvancedSearch->SearchOperator ?: "=";
            $wrk = GetSearchSql($fld, $exportType, $opr, $fld->AdvancedSearch->SearchCondition, $fld->AdvancedSearch->SearchValue2, $fld->AdvancedSearch->SearchOperator2, Config("EXPORT_LOG_DBID"));
            AddFilter($filter, $wrk);
        }
        // Handle tablename
        $fld = $tbl->Fields[Config("EXPORT_LOG_FIELD_NAME_TABLE")];
        $fld->AdvancedSearch->parseSearchValue(Get(Config("EXPORT_LOG_FIELD_NAME_TABLE_ALIAS")));
        $tableName = $fld->AdvancedSearch->SearchValue;
        if (!IsEmpty($tableName)) {
            $zipNames[] = $tableName;
            $opr = $fld->AdvancedSearch->SearchOperator ?: "LIKE";
            $wrk = GetSearchSql($fld, $tableName, $opr, $fld->AdvancedSearch->SearchCondition, $fld->AdvancedSearch->SearchValue2, $fld->AdvancedSearch->SearchOperator2, Config("EXPORT_LOG_DBID"));
            AddFilter($filter, $wrk);
        }
        // Handle filename
        $fld = $tbl->Fields[Config("EXPORT_LOG_FIELD_NAME_FILENAME")];
        $fld->AdvancedSearch->parseSearchValue(Get(Config("EXPORT_LOG_FIELD_NAME_FILENAME_ALIAS")));
        $fileName = $fld->AdvancedSearch->SearchValue;
        if (!IsEmpty($fileName)) {
            $zipNames[] = $fileName;
            $opr = $fld->AdvancedSearch->SearchOperator ?: "LIKE";
            $wrk = GetSearchSql($fld, $fileName, $opr, $fld->AdvancedSearch->SearchCondition, $fld->AdvancedSearch->SearchValue2, $fld->AdvancedSearch->SearchOperator2, Config("EXPORT_LOG_DBID"));
            AddFilter($filter, $wrk);
        }
        // Handle datetime
        $fld = $tbl->Fields[Config("EXPORT_LOG_FIELD_NAME_DATETIME")];
        if (!$fld->AdvancedSearch->get()) {
            $fld->AdvancedSearch->parseSearchValue(Get(Config("EXPORT_LOG_FIELD_NAME_DATETIME_ALIAS")));
        }
        $dt = $fld->AdvancedSearch->SearchValue;
        if (!CheckDate($dt)) {
            $this->response = $this->response->withJson(["success" => false, "error" => sprintf($this->language->phrase("IncorrectDate"), $dt) . ": " . Config("EXPORT_LOG_FIELD_NAME_DATETIME_ALIAS")]);
            return;
        }
        if (!IsEmpty($dt)) {
            $dt = UnformatDateTime($dt, 1);
            $zipNames[] = $dt;
            $opr = $fld->AdvancedSearch->SearchOperator ?: "=";
            if ($opr == "=") {
                $wrk = GetDateFilterSql($fld->Expression, $opr, $dt, $fld->DataType, Config("EXPORT_LOG_DBID"));
            } else {
                $wrk = GetSearchSql($fld, $dt, $opr, $fld->AdvancedSearch->SearchCondition, $fld->AdvancedSearch->SearchValue2, $fld->AdvancedSearch->SearchOperator2, Config("EXPORT_LOG_DBID"));
            }
            AddFilter($filter, $wrk);
        }
        // Validate limit
        $limit = Get(Config("EXPORT_LOG_LIMIT"), 0);
        if ($limit && (!is_numeric($limit) || ParseInteger($limit) <= 0)) {
            $this->response = $this->response->withJson(["success" => false, "error" => $this->language->phrase("IncorrectInteger") . ": " . Config("EXPORT_LOG_LIMIT")]);
            return;
        }
        // Handle limit
        if ($limit) {
            $zipNames[] = $limit;
            $dateTimeField = $tbl->Fields[Config("EXPORT_LOG_FIELD_NAME_DATETIME")];
            $sql = $tbl->getSqlAsQueryBuilder($filter, $dateTimeField->Expression . " DESC")->setMaxResults($limit);
            $rows = Conn($tbl->Dbid)->fetchAllAssociative($sql);
        } elseif (!IsEmpty($filter)) {
            $rows = $tbl->loadRecords($filter)->fetchAllAssociative();
        } else {
            return;
        }
        $fileIds = array_values(array_filter(array_map(fn($t) => $t[Config("EXPORT_LOG_FIELD_NAME_FILE_ID")], $rows), fn($id) => !EmptyValue($id) && CheckGuid($id)));
        if ($output && count($fileIds) >= 1) {
            if (count($fileIds) == 1) { // Single file, just output
                $this->writeExportfile($fileIds[0]);
                return;
            } else { // More than one file, zip for output
                $zip = new ZipArchive();
                $zipFile = ExportPath(true) . implode("_", $zipNames) . ".zip";
                if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
                    foreach ($fileIds as $fileId) {
                        $info = $this->getExportFileByGuid($fileId);
                        if ($info) {
                            $fileName = $info[0];
                            $file = $info[1];
                            if (file_exists($file)) {
                                $zip->addFile($file, $fileName);
                            }
                        }
                    }
                    $zip->close();
                    $this->response = $this->response->withHeader("Content-type", "application/zip")
                        ->withHeader("Content-Disposition", "attachment; filename=\"" . pathinfo($zipFile, PATHINFO_FILENAME) . "\"");
                    $data = file_get_contents($zipFile);
                    $this->response->write($data);
                    @unlink($zipFile);
                    return;
                }
            }
        }
        $this->response = $this->response->withJson(["success" => true, Config("EXPORT_LOG_FIELD_NAME_FILE_ID") => $fileIds]);
    }

    // Write export file
    protected function writeExportFile(string $guid, ?string $fileName = null)
    {
        $info = $this->getExportFileByGuid($guid, $fileName);
        if ($info) {
            $fileName = $info[0];
            $file = $info[1];
            if (file_exists($file) || @fopen($file, "rb") !== false) {
                $ct = MimeContentType($file);
                if ($ct) {
                    $this->response = $this->response->withHeader("Content-type", $ct);
                }
                $data = "";
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (in_array($ext, explode(",", Config("IMAGE_ALLOWED_FILE_EXT")))) { // Skip "Content-Disposition" header if images
                    $data = file_get_contents($file);
                } elseif (in_array($ext, explode(",", Config("DOWNLOAD_ALLOWED_FILE_EXT")))) {
                    $this->response = $this->response->withHeader("Content-Disposition", "attachment" . ($fileName ? "; filename=\"" . $fileName . "\"" : ""));
                    $data = file_get_contents($file);
                }
                $this->response->write($data);
            }
        }
    }

    // Get export file
    protected function getExportFileByGuid(string $guid, ?string $fileName = null): ?array
    {
        $exportLogTable = Config("EXPORT_LOG_TABLE_VAR");
        if (IsEmpty($exportLogTable) || !CheckGuid($guid)) {
            return null;
        }
        $tbl = $this->container->get($exportLogTable);
        $fileIdField = $tbl->Fields[Config("EXPORT_LOG_FIELD_NAME_FILE_ID")];
        $filter = $fileIdField->Expression . " = " . QuotedValue($guid, DataType::GUID, Config("EXPORT_LOG_DBID"));
        $row = $tbl->loadRecords($filter)->fetchAssociative();
        if ($row !== false) {
            $fileName ??= $row[Config("EXPORT_LOG_FIELD_NAME_FILENAME")]; // Get file name
            $table = $row[Config("EXPORT_LOG_FIELD_NAME_TABLE")];
            $this->security->loadTablePermissions($table);
            if (!$this->security->canExport()) {
                throw new HttpForbiddenException($this->request);
            } else {
                $info = pathinfo($fileName);
                $ext = strtolower($info["extension"] ?? "");
                $file = ExportPath(true) . $guid . "." . $ext;
                $file = str_replace("\0", "", $file);
                return [$fileName, $file];
            }
        }
        return null;
    }

    // Export data
    protected function exportData(string $exportType, string $table, ?string $key, bool $output, bool $save)
    {
        global $ExportId, $RenderingView;

        // Set up id for temp folder
        $ExportId = Random();

        // Get table/page class
        $tbl = $this->container->get($table);
        if ($tbl === null) { // Check if valid table
            $this->response = $this->response->withJson(["success" => false, "error" => $this->language->phrase("InvalidParameter") . ": table=" . $table]);
            return;
        }

        // Get record key from query string or form data
        $isList = IsEmpty($key);
        if ($tbl->TableType != "REPORT") { // Skip reports
            if ($isList) { // List/View page
                $recordKeys = $tbl->getRecordKeys();
                $recordKey = count($recordKeys) > 0
                    ? (is_array($recordKeys[0]) ? $recordKeys[0] : [$recordKeys[0]])
                    : [];
                $isList = count($recordKey) == 0 || Param("key_m") !== null; // No key or selected keys
            } else { // View page
                $recordKey = explode(",", $key);
            }
        }

        // Check permission
        $this->security->loadTablePermissions($table);
        if (!$this->security->canExport()) {
            throw new HttpForbiddenException($this->request);
        }

        // Validate export type
        if (
            $tbl->TableType == "REPORT" && !array_key_exists($exportType, Config("REPORT_EXPORT_CLASSES"))
            || !array_key_exists($exportType, Config("EXPORT_CLASSES"))
        ) {
            $this->response = $this->response->withJson(["success" => false, "error" => $this->language->phrase("InvalidParameter") . ": type=" . $exportType]);
            return;
        }

        // Export data
        $doc = null;
        $keyValue = $isList ? "" : implode("_", $recordKey);
        $fileName = Get(Config("API_EXPORT_FILE_NAME"), $tbl->TableVar . ($isList ? "" : "_" . $keyValue));
        $pageName = $tbl->getApiPageName($isList ? Config("API_LIST_ACTION") : Config("API_VIEW_ACTION"));
        $pageClass = PROJECT_NAMESPACE . $pageName;
        $isReport = $tbl->TableType == "REPORT";
        $custom = $isReport;
        $exportClass = Config($isReport ? "REPORT_EXPORT_CLASSES" : "EXPORT_CLASSES")[$exportType];
        if (!class_exists($exportClass)) {
            $exportClass = PROJECT_NAMESPACE . $exportClass;
        }
        if (class_exists($pageClass) && class_exists($exportClass)) {
            $page = $this->container->make($pageClass);
            $page->Export = $exportType;

            // Create export object
            $doc = $this->container->make($exportClass, ["table" => $page]);
            if (!$isReport) {
                $doc->setHorizontal($isList);
            }

            // File ID
            $fileId = "";
            $fileName = $doc->fixFileName($fileName);

            // Make sure export folder exists
            if ($save) {
                CreateDirectory(ExportPath());
            }

            // Export charts
            $files = $this->exportCharts($tbl, $exportType);
            if (is_string($files)) {
                $this->response = $this->response->withJson(["success" => false, "error" => $files]);
                return;
            }

            // Handle custom template (post back)
            $data = Post("data");
            if (IsPost() && $data !== null) {
                $html = $this->replaceCharts($data, $files, $exportType); // Data posted by fetch(), need to convert from utf-8
                $doc->loadHtml($html);
            } else {
                $page->run();
                if ($custom) { // Custom export / Report
                    if (!$page->isTerminated()) {
                        $template = ($page->View ?? $pageName) . ".php"; // View
                        $GLOBALS["Title"] ??= $page->Title; // Title
                        $RenderingView = true;
                        try {
                            $view = $this->container->get("app.view");
                            $this->response = $view->render($this->response, $template, $GLOBALS); // Render view with $GLOBALS
                            $html = $this->replaceCharts($this->response->getBody(), $files, $exportType);
                            $doc->loadHtml($html);
                            $this->response = ResponseFactory()->createResponse(); // Reset
                        } finally {
                            $RenderingView = false;
                            $page->terminate(true); // Terminate page and clean up
                        }
                    }
                } else { // Table export
                    if ($isList) { // List page
                        // Add top/left charts
                        foreach ($files as $id => $file) {
                            $chart = $tbl->Charts[$id] ?? null;
                            if ($chart && $chart->Position <= 2) {
                                $doc->addImage($file, "after");
                            }
                        }
                        // Export
                        $page->exportData($doc);
                        // Add right/bottom charts
                        foreach ($files as $id => $file) {
                            $chart = $tbl->Charts[$id] ?? null;
                            if ($chart && $chart->Position > 2) {
                                $doc->addImage($file, "before");
                            }
                        }
                    } else { // View page
                        $page->exportData($doc, $recordKey);
                    }
                }
            }

            // Export
            $result = $doc->export($fileName, $output, $save);
            $fileId = "";
            if ($exportType == "email") { // Return email result
                $this->response = $this->response->withJson($result);
            } elseif ($save) {
                // Get file ID
                $fileId = $doc->getFileId();
                // Return file ID if export file not returned
                if (!$output) {
                    $this->response = $this->response->withJson(["success" => true, "fileId" => $fileId]);
                }
            }
            // Write export log
            if ($fileId || Config("LOG_ALL_EXPORT_REQUESTS")) {
                if (!$fileId) { // Create unique key for file Id
                    $fileId = DbCurrentDateTime() . "/" . CurrentUserIdentifier() . "/" . $exportType . "/" . $table;
                    if (strlen($fileId) > 36) {
                        $fileId = substr($fileId, 0, 36);
                    }
                }
                WriteExportLog($fileId, DbCurrentDateTime(), CurrentUserIdentifier(), $exportType, $table, $keyValue, $fileName, ServerVar("REQUEST_URI"));
            }
        } else {
            $this->response = $this->response->withJson(["success" => false, "message" => $this->language->phrase("InvalidParameter") . ": table = " . $table . ", export type = " . $exportType]);
        }

        // Clean up export files
        if (Config("EXPORT_FILES_EXPIRY_TIME") > 0) {
            CleanPath(PrefixDirectoryPath(ExportPath(true)), false, "< now - " . Config("EXPORT_FILES_EXPIRY_TIME") . " minutes");
        }

        // Delete temp images
        CleanPath(PrefixDirectoryPath(UploadTempPath()), true);
    }

    // Export charts
    public function exportCharts(mixed $tbl, string $exportType): string|array
    {
        $json = Post("charts", "[]");
        $charts = json_decode($json);
        $files = [];
        foreach ($charts as $chart) {
            $img = false;
            // Charts base64
            if ($chart->streamType == "base64") {
                try {
                    $img = base64_decode(preg_replace('/^data:image\/\w+;base64,/', "", $chart->stream));
                } catch (Throwable $e) {
                    return $e->getMessage();
                }
            }
            if ($img === false) {
                return sprintf($this->language->phrase("ChartExportError1"), $chart->streamType, $chart->chartEngine);
            }
            // Save the file
            $filename = $chart->fileName;
            if ($filename == "") {
                return $this->language->phrase("ChartExportError2");
            }
            $path = UploadTempPath();
            if (!CreateDirectory($path)) {
                return $this->language->phrase("ChartExportError3");
            }
            if (!is_writable(PrefixDirectoryPath($path))) {
                return $this->language->phrase("ChartExportError4");
            }
            $filepath = IncludeTrailingDelimiter($path, false) . $filename;
            $id = preg_replace('/^chart_/', '', pathinfo($filepath, PATHINFO_FILENAME));
            $this->resizeAndSaveChart($tbl, $img, $exportType, $filepath);
            $files[$id] = $filepath;
        }

        // Return file array
        return $files;
    }

    // Resize and save chart image
    public function resizeAndSaveChart(mixed $tbl, mixed $img, string $exportType, string $filepath): bool
    {
        $exportPdf = ($exportType == "pdf");
        $exportWord = ($exportType == "word" && Config("USE_PHPWORD"));
        $exportExcel = ($exportType == "excel" && Config("USE_PHPEXCEL"));
        $dimension = $this->chartDimension($tbl, $img, $exportType);
        if (($exportPdf || $exportWord || $exportExcel) && $dimension["width"] > 0 && $dimension["height"] > 0) {
            ResizeBinary($img, $dimension["width"], $dimension["height"], resizeOptions: ["keepAspectRatio" => true]); // Keep aspect ratio for chart
        }
        WriteFile($filepath, $img);
        return true;
    }

    // Get chart export width and height
    public function chartDimension(mixed $tbl, mixed $img, string $exportType): array
    {
        $portrait = SameText($tbl->ExportPageOrientation, "portrait");
        $exportPdf = ($exportType == "pdf");
        $exportWord = ($exportType == "word" && Config("USE_PHPWORD"));
        $exportExcel = ($exportType == "excel" && Config("USE_PHPEXCEL"));
        if ($exportPdf) {
            $maxWidth = $portrait ? Config("PDF_MAX_IMAGE_WIDTH") : Config("PDF_MAX_IMAGE_HEIGHT");
            $maxHeight = $portrait ? Config("PDF_MAX_IMAGE_HEIGHT") : Config("PDF_MAX_IMAGE_WIDTH");
        } elseif ($exportWord) {
            global $WORD_MAX_IMAGE_WIDTH, $WORD_MAX_IMAGE_HEIGHT;
            $maxWidth = $portrait ? $WORD_MAX_IMAGE_WIDTH : $WORD_MAX_IMAGE_HEIGHT;
            $maxHeight = $portrait ? $WORD_MAX_IMAGE_HEIGHT : $WORD_MAX_IMAGE_WIDTH;
        } elseif ($exportExcel) {
            global $EXCEL_MAX_IMAGE_WIDTH, $EXCEL_MAX_IMAGE_HEIGHT;
            $maxWidth = $portrait ? $EXCEL_MAX_IMAGE_WIDTH : $EXCEL_MAX_IMAGE_HEIGHT;
            $maxHeight = $portrait ? $EXCEL_MAX_IMAGE_HEIGHT : $EXCEL_MAX_IMAGE_WIDTH;
        }
        if ($exportPdf || $exportWord || $exportExcel) {
            $size = @getimagesizefromstring($img);
            $w = (@$size[0] > 0) ? min($size[0], $maxWidth) : $maxWidth;
            $h = (@$size[1] > 0) ? min($size[1], $maxHeight) : $maxHeight;
            return ["width" => $w, "height" => $h];
        }
        return ["width" => 0, "height" => 0];
    }

    // Replace charts in custom template
    public function replaceCharts(string $text, array $files, string $exportType): string
    {
        $doc = new Document(null, false, PROJECT_ENCODING); // Note: This will add <body> tag
        @$doc->load($text);
        $charts = $doc->find(".ew-chart");
        foreach ($charts as $chart) {
            $id = preg_replace('/^div_cht_/', '', $chart->getAttribute("id"));
            $file = $files[$id] ?? null;
            if ($file) {
                $div = $doc->createElement("div");
                $div->setAttribute("class", $chart->getAttribute("class")); // Copy classes, e.g. "ew-chart break-before-page"
                $img = $doc->createElement("img");
                $size = @getimagesize($file);
                $img->setAttribute("src", ImageFileToBase64Url($file));
                if (@$size[0] > 0) {
                    $img->setAttribute("width", $size[0]);
                }
                if (@$size[1] > 0) {
                    $img->setAttribute("height", $size[1]);
                }
                $div->appendChild($img);
                $chart->replace($div);
            }
        }
        return $doc->first("body")->innerHtml();
    }
}
