<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * File viewer
 */
class FileViewer
{
    // Constructor
    public function __construct(protected ResponseFactoryInterface $responseFactory) {
    }

    /**
     * Output file
     *
     * @return bool Whether file is outputted successfully
     */
    public function __invoke(): Response
    {
        // Get parameters
        $tbl = null;
        $tableName = "";
        if (IsPost()) {
            $fn = Post("fn", "");
            $table = Post(Config("API_OBJECT_NAME"), "");
            $field = Post(Config("API_FIELD_NAME"), "");
            $recordkey = Post(Config("API_KEY_NAME"), "");
            $resize = Post("resize", "0") == "1";
            $width = Post("width", 0);
            $height = Post("height", 0);
            $download = Post("download", "1") == "1"; // Download by default
            $crop = Post("crop", "");
        } else { // api/file/object/field/key
            $fn = Get("fn", "");
            if (!empty(Route("param")) && empty(Route("key"))) {
                $fn = Route("param");
            }
            $table = Get(Config("API_OBJECT_NAME"), Route("table"));
            $field = Get(Config("API_FIELD_NAME"), Route("param"));
            $recordkey = Get(Config("API_KEY_NAME"), Route("key"));
            $resize = Get("resize", "0") == "1";
            $width = Get("width", 0);
            $height = Get("height", 0);
            $download = Get("download", "1") == "1"; // Download by default
            $crop = Get("crop", "");
        }
        $key = SessionId() . Config("ENCRYPTION_KEY");
        if (!is_numeric($width)) {
            $width = 0;
        }
        if (!is_numeric($height)) {
            $height = 0;
        }
        if ($width == 0 && $height == 0 && $resize) {
            $width = Config("THUMBNAIL_DEFAULT_WIDTH");
            $height = Config("THUMBNAIL_DEFAULT_HEIGHT");
        }

        // Get table object
        $tbl = Container($table);

        // API request with table/fn
        $fn = ($tbl?->TableName ?? false)
            ? Decrypt($fn, $key) // File path is always encrypted
            : "";

        // Get image
        $res = false;
        $func = fn($phpthumb) => $phpthumb->adaptiveResize($width, $height);
        $plugins = $crop ? [$func] : [];
        $response = $this->responseFactory->createResponse();
        if ($fn != "") { // Physical file
            $fn = str_replace("\0", "", $fn);
            $info = pathinfo($fn);
            if ($data = ReadFile($fn)) {
                $ext = strtolower($info["extension"] ?? "");
                $isPdf = SameText($ext, "pdf");
                $ct = MimeContentType($fn);
                if ($ct) {
                    $response = $response->withHeader("Content-type", $ct);
                }
                if (in_array($ext, explode(",", Config("IMAGE_ALLOWED_FILE_EXT")))) { // Skip "Content-Disposition" header if images
                    if ($width > 0 || $height > 0) {
                        ResizeBinary($data, $width, $height, plugins: $plugins);
                    }
                } elseif (in_array($ext, explode(",", Config("DOWNLOAD_ALLOWED_FILE_EXT")))) {
                    $isAttachment = false;
                    if ($download && !((Config("EMBED_PDF") || !Config("DOWNLOAD_PDF_FILE")) && $isPdf)) { // Skip header if embed/inline PDF
                        $isAttachment = true;
                    }
                    // Add filename in Content-Disposition
                    $response = $response->withHeader("Content-Disposition", ($isAttachment ? "attachment" : "inline") . "; filename=\"" . $info["basename"] . "\"");
                }
                return $response->write($data);
            }
        } elseif (is_object($tbl) && $field != "" && $recordkey != "") { // From table
            return $tbl->getFileData($field, $recordkey, $resize, $width, $height, $plugins);
        }
        return $response;
    }
}
