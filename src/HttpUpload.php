<?php

namespace PHPMaker2025\ucarsip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

/**
 * Upload class
 */
class HttpUpload
{
    protected Language $language;
    public array $UploadedFiles = []; // Uploaded files (array)
    public int|string $Index = -1; // Index for multiple form elements
    public string $UploadPath = ""; // Upload path
    public string $Message = ""; // Error message
    public mixed $DbValue = null; // Value from database
    public mixed $Value = null; // Upload value
    public ?string $FileName = null; // Upload file name
    public int $FileSize = 0; // Upload file size
    public string $ContentType = ""; // File content type
    public int $ImageWidth = 0; // Image width
    public int $ImageHeight = 0; // Image height
    public string $Error = ""; // Upload error
    public bool $UploadMultiple = false; // Multiple upload
    public bool $KeepFile = true; // Keep old file
    public array $Plugins = []; // Plugins for Resize()

    // Constructor
    public function __construct(
        protected ?DbField $parent = null, // Parent field object
        protected ?Request $request = null
    ) {
        $this->language = Language();
    }

    // Create a new instance
    public static function create(?DbField $parent = null, ?Request $request = null): static
    {
        return new static($parent, $request);
    }

    // Check file type of the uploaded file
    public function uploadAllowedFileExt(string $filename): bool
    {
        return CheckFileType($filename);
    }

    // Get upload file
    public function uploadFile(): bool
    {
        $this->Value = null; // Reset first

        // Get file from token or FormData for API request
        // - NOTE: for add option, use normal path as file is already uploaded in session
        $fldvar = ($this->Index < 0) ? $this->parent->FieldVar : substr($this->parent->FieldVar, 0, 1) . $this->Index . substr($this->parent->FieldVar, 1);
        if (IsApi() && Post("addopt") != "1") {
            $this->setupTempDirectory($this->Index); // Set up temp folder
            $oldFileName = strval($this->FileName);
            if ($this->getUploadedFiles($this->parent, false)) { // Try to get from FormData / Token
                $this->KeepFile = false;
            }
            $wrkvar = "fn_" . $fldvar;
            if (($fileNames = Post($wrkvar)) !== null) { // Get post back file names
                if ($this->parent->DataType != DataType::BLOB || empty($fileNames)) { // Non blob or delete file action
                    $this->FileName = $fileNames;
                }
            }
            if (!SameString(strval($this->FileName), $oldFileName)) { // File names changed
                $this->KeepFile = false;
            }
        } else {
            $wrkvar = "fn_" . $fldvar;
            $this->FileName = Post($wrkvar, ""); // Get file name
            $wrkvar = "fa_" . $fldvar;
            $this->KeepFile = (Post($wrkvar, "") == "1"); // Check if keep old file
        }
        if (!$this->KeepFile && $this->FileName != "" && !$this->UploadMultiple) {
            $f = UploadTempPath($this->parent, $this->Index) . $this->FileName;
            if (FileExists($f)) {
                $this->Value = ReadFile($f);
                $this->FileSize = GetFileSize($f);
                $this->ContentType = ContentType($this->Value, $f);
                $sizes = @getimagesize($f);
                $this->ImageWidth = $sizes[0] ?? 0;
                $this->ImageHeight = $sizes[1] ?? 0;
            }
        }
        return true; // Normal return
    }

    // Set up temp directory
    public function setupTempDirectory(int $idx = -1): void
    {
        $fld = $this->parent;
        if ($fld?->Table?->EventCancelled) { // Skip render if insert/update cancelled
            return;
        }
        $folder = UploadTempPath($fld, $idx);
        CleanPath(PrefixDirectoryPath($folder)); // Clean the upload folder
        $thumbnailfolder = PathCombine($folder, Config("UPLOAD_THUMBNAIL_FOLDER"), false);
        $imageFileTypes = explode(",", Config("IMAGE_ALLOWED_FILE_EXT"));
        if ($fld->DataType == DataType::BLOB) { // Blob field
            $data = $this->DbValue;
            if (!IsEmpty($data)) {
                // Create upload file
                $filename = ($this->FileName != "") ? $this->FileName : $fld->Param;
                $f = IncludeTrailingDelimiter($folder, false) . $filename;
                $this->createTempFile($f, $data);
                // Create thumbnail file
                $f = IncludeTrailingDelimiter($thumbnailfolder, false) . $filename;
                $ext = ContentExtension($data);
                if ($ext != "" && in_array(substr($ext, 1), $imageFileTypes)) {
                    $width = Config("UPLOAD_THUMBNAIL_WIDTH");
                    $height = Config("UPLOAD_THUMBNAIL_HEIGHT");
                    ResizeBinary($data, $width, $height);
                    $this->createTempFile($f, $data);
                }
                $this->FileName = basename($f); // Update file name
            }
        } else { // Upload to folder
            $this->FileName = $fld->htmlDecode($this->DbValue); // Update file name
            if (!IsEmpty($this->FileName)) {
                // Create upload file
                if ($fld->UploadMultiple) {
                    $files = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $this->FileName);
                } else {
                    $files = [$this->FileName];
                }
                foreach ($files as $filename) {
                    if ($filename != "") {
                        $pathinfo = pathinfo($filename);
                        $dirname = $pathinfo["dirname"] ?? "";
                        $filename = $pathinfo["basename"];
                        $ext = strtolower($pathinfo["extension"] ?? "");
                        $filepath = ($dirname != "" && $dirname != ".") ? PathCombine($fld->UploadPath, $dirname, false) : $fld->UploadPath;
                        $srcfile = IncludeTrailingDelimiter($filepath, false) . $filename;
                        $storage = GetRemotePathInfo($fld->uploadPath())["storage"];
                        $f = IncludeTrailingDelimiter($folder, false) . $filename;
                        $tf = IncludeTrailingDelimiter($thumbnailfolder, false) . $filename;
                        if (FileExists($srcfile, $storage)) { // File found
                            $data = ReadFile($srcfile, $storage);
                            if (!$ext) {
                                $ext = ContentExtension($data, false);
                            }
                            $this->createTempFile($f, $data);
                            if (in_array($ext, $imageFileTypes)) {
                                $w = Config("UPLOAD_THUMBNAIL_WIDTH");
                                $h = Config("UPLOAD_THUMBNAIL_HEIGHT");
                                ResizeBinary($data, $w, $h); // Resize as thumbnail
                                $this->createTempFile($tf, $data); // Create thumbnail
                            }
                        } else { // File not found
                            $data = Config("FILE_NOT_FOUND");
                            $this->createTempFile($f, base64_decode($data));
                        }
                    }
                }
            }
        }
    }

    // Create temp file
    private function createTempFile(string $f, string $data): void
    {
        $pathinfo = pathinfo($f);
        $extension = $pathinfo["extension"] ?? "";
        if ($extension == "") { // No file extension
            $extension = ContentExtension($data);
            if ($extension) {
                $f .= $extension;
            }
        }
        WriteFile($f, $data);
    }

    // Get uploaded files
    public function getUploadedFiles(?DbField $fld = null, bool $output = true): array|bool
    {
        $res = true;
        $this->UploadedFiles = is_object($this->request) ? $this->request->getUploadedFiles() : [];
        $files = [];

        // Validate request
        if (!is_array($this->UploadedFiles)) {
            return $output
                ? ["success" => false, "error" => "No uploaded files"]
                : false;
        }

        // Create temp folder
        $filetoken = strval(Random());
        if ($fld === null) { // API file upload request
            $path = UploadTempPath($filetoken, $this->Index);
        } else {
            $path = UploadTempPath($fld, $this->Index);
        }
        if (!CreateDirectory($path)) {
            return $output
                ? ["success" => false, "error" => "Create folder '" . $path . "' failed."]
                : false;
        }

        // Move files to temp folder
        $fileName = "";
        $fileTypes = '/\\.(' . (Config("UPLOAD_ALLOWED_FILE_EXT") != "" ? str_replace(",", "|", Config("UPLOAD_ALLOWED_FILE_EXT")) : "[\s\S]+") . ')$/i';

        // Process multi part upload
        $name = $fld?->Name ?? "";
        foreach ($this->UploadedFiles as $id => $uploadedFiles) {
            if ($id == $name || $name == "") {
                $ar = [];
                $single = is_object($uploadedFiles); // Single upload
                if ($single) {
                    $uploadedFiles = [$uploadedFiles];
                }
                if (is_array($uploadedFiles)) { // Multiple upload
                    foreach ($uploadedFiles as $uploadedFile) {
                        if ($fileName != "") {
                            $fileName .= Config("MULTIPLE_UPLOAD_SEPARATOR");
                        }
                        $clientFilename = $uploadedFile->getClientFilename();
                        $fileSize = $uploadedFile->getSize();
                        $fileName .= $clientFilename;
                        $arwrk = ["name" => $clientFilename];
                        if (!preg_match($fileTypes, $clientFilename)) { // Check file extensions
                            $arwrk["success"] = false;
                            $arwrk["error"] = $this->language->phrase("UploadErrorAcceptFileTypes");
                            $res = false;
                        } elseif (Config("MAX_FILE_SIZE") > 0 && $fileSize > Config("MAX_FILE_SIZE")) { // Check file size
                            $arwrk["success"] = false;
                            $arwrk["error"] = $this->language->phrase("UploadErrorMaxFileSize");
                            $res = false;
                        } elseif ($this->moveUploadedFile($uploadedFile, $path)) {
                            $arwrk["success"] = true;
                        } else {
                            $arwrk["success"] = false;
                            $arwrk["error"] = $uploadedFile->getError();
                            if (in_array($arwrk["error"], range(1, 8))) {
                                $arwrk["error"] = $this->language->phrase("UploadError" . $arwrk["error"]);
                            }
                            $res = false;
                        }
                        $ar[] = $arwrk;
                    }
                }
                if ($single) {
                    $ar = $ar[0];
                }
                $files[$id] = $ar;
            }
        }

        // Process file token (uploaded in previous API file upload request)
        if ($name != "" && Post($name) !== null) {
            $token = Post($name);
            $tokenPath = UploadTempPath($token, $this->Index);
            try {
                if (@is_dir($tokenPath) && ($dh = opendir($tokenPath))) {
                    // Get all files in the folder
                    while (($file = readdir($dh)) !== false) {
                        if ($file == "." || $file == ".." || !is_file($tokenPath . $file)) {
                            continue;
                        }
                        if (file_exists($path . $file)) { // Delete old file first
                            @unlink($path . $file);
                        }
                        rename($tokenPath . $file, $path . $file); // Move to temp folder
                        if ($fileName != "") {
                            $fileName .= Config("MULTIPLE_UPLOAD_SEPARATOR");
                        }
                        $fileName .= $file;
                    }
                    CleanUploadTempPath($tokenPath, $this->Index); // Clean up
                }
            } catch (Throwable $e) {
                if (IsDebug()) {
                    throw $e;
                }
            }
        }
        $res = $fileName != "";
        $result = ["success" => $res, "files" => $files];
        if ($res) { // Add token if any file uploaded successfully
            $this->FileName = $fileName;
            $result[Config("API_FILE_TOKEN_NAME")] = $filetoken;
        } else { // All failed => clean path
            if ($fld === null) { // API file upload request
                CleanPath(PrefixDirectoryPath($path), true);
            }
        }
        return $output ? $result : $res;
    }

    /**
     * Get uploaded file names (with or without full path)
     *
     * @param string $filetoken File token to locate the uploaded temp path
     * @param bool $path Return file name with or without full path
     * @return array
     */
    public function getUploadedFileNames(string $filetoken, bool $fullPath = false): array
    {
        if (IsEmpty($filetoken)) { // Remove
            return [];
        } else { // Load file name from token
            $path = UploadTempPath($filetoken, $this->Index);
            try {
                if (@is_dir($path) && ($dh = opendir($path))) {
                    $fileNames = [];
                    while (($file = readdir($dh)) !== false) { // Get all files in the folder
                        if ($file == "." || $file == ".." || !is_file($path . $file)) {
                            continue;
                        }
                        $fileNames[] = $fullPath ? $path . $file : $file;
                    }
                    return $fileNames;
                }
            } catch (Throwable $e) {
                if (IsDebug()) {
                    throw $e;
                }
            }
            return [];
        }
    }

    /**
     * Get uploaded file names (with or without full path)
     *
     * @param string $filetoken File token to locate the uploaded temp path
     * @param bool $path Return file name with or without full path
     * @return string
     */
    public function getUploadedFileName(string $filetoken, bool $fullPath = false): string
    {
        return implode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $this->getUploadedFileNames($filetoken, $fullPath));
    }

    /**
     * Resize image
     *
     * @param int $width Target width of image
     * @param int $height Target height of image
     * @param int $quality Deprecated, kept for backward compatibility only.
     * @return HttpUpload
     */
    public function resize(int $width, int $height, int $quality = 100): static
    {
        if (!IsEmpty($this->Value)) {
            $wrkwidth = $width;
            $wrkheight = $height;
            if (ResizeBinary($this->Value, $wrkwidth, $wrkheight, plugins: $this->Plugins)) {
                if ($wrkwidth > 0 && $wrkheight > 0) {
                    $this->ImageWidth = $wrkwidth;
                    $this->ImageHeight = $wrkheight;
                }
                $this->FileSize = strlen($this->Value);
            }
        }
        return $this;
    }

    /**
     * Get file count
     */
    public function count(): int
    {
        if (!$this->UploadMultiple && !IsEmpty($this->Value)) {
            return 1;
        } elseif ($this->UploadMultiple && $this->FileName != "") {
            $ar = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $this->FileName);
            return count($ar);
        }
        return 0;
    }

    /**
     * Get temp file
     *
     * @param int $idx
     * @return object|object[] Instance(s) of thumbnail class (Config("THUMBNAIL_CLASS"))
     */
    public function getTempThumb(int $idx = -1): mixed
    {
        $file = $this->getTempFile($idx);
        $cls = Config("THUMBNAIL_CLASS");
        if (is_string($file)) {
            return file_exists($file) ? new $cls($file, Config("RESIZE_OPTIONS"), $this->Plugins) : null;
        } elseif (is_array($file)) {
            $thumbs = [];
            foreach ($file as $fn) {
                if (file_exists($fn)) {
                    $thumbs[] = new $cls($fn, Config("RESIZE_OPTIONS"), $this->Plugins);
                }
            }
            return $thumbs;
        }
        return null;
    }

    /**
     * Save uploaded data to file
     *
     * @param string $newFileName New file name
     * @param bool $overWrite Overwrite existing file or not
     * @param int $idx Index of file
     * @return bool
     */
    public function saveToFile(string $newFileName, bool $overWrite, int $idx = -1): bool
    {
        $path = IncludeTrailingDelimiter($this->UploadPath ?: $this->parent->UploadPath, false);
        if (!IsEmpty($this->Value)) {
            if (trim(strval($newFileName)) == "") {
                $newFileName = $this->FileName;
            }
            if (!$overWrite) {
                $newFileName = UniqueFilename($path, $newFileName);
            }
            return WriteFile($path . $newFileName, $this->Value);
        } elseif ($idx >= 0) { // Use file from upload temp folder
            $file = $this->getTempFile($idx);
            if (FileExists($file)) {
                if (!$overWrite) {
                    $newFileName = UniqueFilename($path, $newFileName);
                }
                return CopyFile($file, $path . $newFileName);
            }
        }
        return false;
    }

    /**
     * Resize and save uploaded data to file
     *
     * @param int $width Target width of image
     * @param int $height Target height of image
     * @param int $quality Deprecated, kept for backward compatibility only.
     * @param string $newFileName New file name
     * @param bool $overWrite Overwrite existing file or not
     * @param int $idx optional Index of the file
     * @return bool
     */
    public function resizeAndSaveToFile(int $width, int $height, int $quality, string $newFileName, bool $overWrite, int $idx = -1): bool
    {
        $numargs = func_num_args();
        $args = func_get_args();
        $oldPath = $this->UploadPath;
        if ($numargs >= 6 && is_string($args[4])) { // resizeAndSaveToFile($width, $height, $quality, $path, $newFileName, $overWrite, $idx = -1)
            $this->UploadPath = $args[3]; // Relative to app root
            $newFileName = $args[4];
            $overWrite = $args[5];
            $idx = ($numargs > 6) ? $args[6] : -1;
        }
        $result = false;
        if (!IsEmpty($this->Value)) {
            $oldValue = $this->Value;
            $result = $this->resize($width, $height)->saveToFile($newFileName, $overWrite, $idx);
            $this->Value = $oldValue;
        } elseif ($idx >= 0) { // Use file from upload temp folder
            $file = $this->getTempFile($idx);
            if (FileExists($file)) {
                $this->Value = ReadFile($file);
                $result = $this->resize($width, $height)->saveToFile($newFileName, $overWrite);
                $this->Value = null;
            }
        }
        $this->UploadPath = $oldPath;
        return $result;
    }

    // Move upload file
    protected function moveUploadedFile(object $uploadedFile, string $path): bool
    {
        $uploadFileName = $uploadedFile->getClientFilename();
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $uploadedFile->moveTo($path . $uploadFileName);
            return true;
        }
        return false;
    }

    /**
     * Get temp file path
     *
     * @param int $idx optional Index of file
     * @return string|string[]
     */
    public function getTempFile(int $idx = -1): mixed
    {
        if ($this->FileName != "") {
            $path = UploadTempPath($this->parent, $this->Index);
            if ($this->UploadMultiple) {
                $ar = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $this->FileName);
                if ($idx > -1 && $idx < count($ar)) {
                    return $path . $ar[$idx];
                } else {
                    $files = [];
                    foreach ($ar as $fn) {
                        $files[] = $path . $fn;
                    }
                    return $files;
                }
            } else {
                return $path . $this->FileName;
            }
        }
        return null;
    }
}
