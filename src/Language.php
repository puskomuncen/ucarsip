<?php

namespace PHPMaker2025\ucarsip;

use Dflydev\DotAccessData\Data;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\VarExporter\VarExporter;
use Exception;

/**
 * Langauge class
 */
class Language
{
    public static bool $SortByName = false;
    public static bool $SortByCaseInsensitiveName = false;
    public static bool $SortBySize = false;
    public static bool $ReverseSorting = false;
    public static bool $UseCache = false;
    public static string $CACHE_FILE = "LanguageCache.*.php"; // Language file under CACHE_FOLDER
    public Data $Data;
    public string $LanguageId = "";
    public string $LanguageFolder = "";
    public string $Template = ""; // JsRender template
    public string $Method = "prependTo"; // JsRender template method
    public string $Target = ".navbar-nav.ms-auto"; // JsRender template target
    // public string $Type = "LI"; // LI/DROPDOWN (for used with top Navbar) or SELECT/RADIO (NOT for used with top Navbar)
	public string $Type = "DROPDOWN"; // available: LI, DROPDOWN, SELECT, or RADIO
	public int $LanguageCount = 0; // language count, by Masino Sinaga, April 26, 2022

    // Constructor
    public function __construct()
    {
        global $Language;
        $Language = $this;
        $this->setLanguage(Param("language"));
    }

    // Set language
    public function setLanguage(?string $langId = null): void
    {
        global $CurrentLanguage;
        $this->LanguageFolder = Config("LANGUAGE_FOLDER");
        if ($langId) {
            $this->LanguageId = $langId;
            Session(SESSION_LANGUAGE_ID, $this->LanguageId);
        } elseif (Session(SESSION_LANGUAGE_ID) != "") {
            $this->LanguageId = Session(SESSION_LANGUAGE_ID);
        } else {
            $this->LanguageId = Config("DEFAULT_LANGUAGE_ID");
        }
        $CurrentLanguage = $this->LanguageId;
        $this->loadLanguage($this->LanguageId);

        // Dispatch event
        DispatchEvent(new LanguageLoadEvent($this), LanguageLoadEvent::NAME);
        SetClientVar("languages", ["languages" => $this->getLanguages()]);
    }

    // Parse XML
    protected function parseXml(string $xml, mixed &$values): void
    {
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); // Always return in utf-8
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $values);
        $errorCode = xml_get_error_code($parser);
        if ($errorCode > 0) {
            throw new Exception(xml_error_string($errorCode));
        }
        xml_parser_free($parser);
    }

    /**
     * Load XML
     * <ew-language> // level 1
     *     <global> // level 2
     *         <phrase/> // level 3
     *         <extension> // level 3
     *             <phrase/> // level 4
     * @param string $xml XML
     * @return Data
     */
    protected function loadXml(string $xml): Data
    {
        $data = new Data();
        $xml = trim($xml);
        if (!$xml) {
            return $data;
        }
        $this->parseXml(trim($xml), $xmlValues);
        if (!is_array($xmlValues)) {
            return $data;
        }
        $tags = [];
        foreach ($xmlValues as $xmlValue) {
            $attributes = null; // Reset attributes first
            extract($xmlValue); // Extract as $tag (string), $type (string), $level (int) and $attributes (array)
            if ($level == 1) {
                continue; // Skip root tag
            }
            if ($type == "open" || $type == "complete") { // Open tag like '<tag ...>' or complete tag like '<tag/>'
                if ($attributes["id"] ?? false) { // Has "id" attribute
                    $convert = fn ($id) => ($tags[2] ?? "") == "global" && $level > 3 // Extension phrases
                        ? $id // Keep the id as camel case as JavaScript
                        : strtolower($id);
                    if ($type == "open") {
                        $tag .= "." . $convert($attributes["id"]); // Convert id to lowercase
                    } elseif ($type == "complete") { // <phrase/>
                        $tag = $convert($attributes["id"]); // Convert id to lowercase
                    }
                    unset($attributes["id"]);
                }
                $tags[$level] = $tag;
                if (is_array($attributes) && count($attributes) > 0 && $level > 1) {
                    $data->set(implode(".", array_filter(array_slice($tags, 0, $level - 1))), $attributes);
                }
            }
        }
        return $data;
    }

    // Get cache folder
    protected function getCacheFolder(): string
    {
        return IncludeTrailingDelimiter(Config("CACHE_FOLDER"), false);
    }

    // Load language file(s)
    protected function loadLanguage(string $id): void
    {
        global $CURRENCY_CODE, $CURRENCY_SYMBOL, $DECIMAL_SEPARATOR, $GROUPING_SEPARATOR,
            $NUMBER_FORMAT, $CURRENCY_FORMAT, $PERCENT_SYMBOL, $PERCENT_FORMAT, $NUMBERING_SYSTEM,
            $DATE_FORMAT, $TIME_FORMAT, $DATE_SEPARATOR, $TIME_SEPARATOR, $TIME_ZONE;
        $cacheFile = str_replace("*", $id, $this->getCacheFolder() . self::$CACHE_FILE);
        if (self::$UseCache && !IsRemote($cacheFile) && FileExists($cacheFile)) {
            $this->Data = new Data(require $cacheFile);
        } else {
            $this->Data = new Data();
            $finder = new Finder();
            $finder->files()->in($this->LanguageFolder)->name("*.$id.xml"); // Find all *.$id.xml
            if (!$finder->hasResults()) {
                LogError("Missing language files for language ID '$id'");
                $finder->files()->in($this->LanguageFolder)->name("*.en-US.xml"); // Fallback to en-US
            }
            if (self::$SortBySize && method_exists($finder, "sortBySize")) {
                $finder->sortBySize();
            }
            if (self::$SortByName) {
                $finder->sortByName();
            }
            if (self::$SortByCaseInsensitiveName) {
                if (method_exists($finder, "sortByCaseInsensitiveName")) {
                    $finder->sortByCaseInsensitiveName();
                } else {
                    $finder->sortByName();
                }
            }
            if (self::$ReverseSorting) {
                $finder->reverseSorting();
            }
            foreach ($finder as $file) {
                try {
                    $this->Data->importData($this->loadXml($file->getContents()));
                } catch (Exception $e) {
                    Session()->remove(SESSION_LANGUAGE_ID); // Clear the saved language ID from session
                    throw new Exception("Error occurred when parsing " . $file->getFilename() . ": " . $e->getMessage() . ". Make sure it is well-formed.");
                }
            }
            if (self::$UseCache && CreateDirectory($this->getCacheFolder())) {
                WriteFile($cacheFile, "<?php return " . VarExporter::export($this->Data->export()) . ";");
            }
        }

        // Set up locale for the language
        $locale = LocaleConvert();
        $CURRENCY_CODE = $locale["currency_code"];
        $CURRENCY_SYMBOL = $locale["currency_symbol"];
        $DECIMAL_SEPARATOR = $locale["decimal_separator"];
        $GROUPING_SEPARATOR = $locale["grouping_separator"];
        $NUMBER_FORMAT = $locale["number"];
        $CURRENCY_FORMAT = $locale["currency"];
        $PERCENT_SYMBOL = $locale["percent_symbol"];
        $PERCENT_FORMAT = $locale["percent"];
        $NUMBERING_SYSTEM = $locale["numbering_system"];
        $DATE_FORMAT = $locale["date"];
        $TIME_FORMAT = $locale["time"];
        $DATE_SEPARATOR = $locale["date_separator"];
        $TIME_SEPARATOR = $locale["time_separator"];
        $TIME_ZONE = $locale["time_zone"];

        // Set up time zone from locale file (see https://www.php.net/timezones for supported time zones)
        if (!empty($TIME_ZONE)) {
            date_default_timezone_set($TIME_ZONE);
        }

        // Save to session (for extension)
        Session(["_Language" => $this->LanguageId, "_TimeZone" => $TIME_ZONE]);
    }

    // Get value only
    protected function getValue(array $data): array|string
    {
        $collect = Collection::make($data);
        if ($collect->count() > 0) {
            if ($collect->every(fn ($v) => is_array($v))) { // Array of array
                return $collect->map(fn ($v) => $this->getValue($v))->all();
            }
            return $collect->get("value") ?? "";
        }
        return "";
    }

    // Has data
    public function hasData(string $id): bool
    {
        return $this->Data->has(strtolower($id ?? ""));
    }

    // Set data
    public function setData(string $id, string|array $value): void
    {
        $this->Data->set(strtolower($id ?? ""), $value);
    }

    // Get data
    public function getData(string $id): string|array
    {
        return $this->Data->get(strtolower($id ?? ""), "");
    }

    /**
     * Get phrase
     *
     * @param string $id Phrase ID
     * @param ?bool $useText (true => text only, false => icon only, null => both)
     * @return string|array
     */
    public function phrase(string $id, ?bool $useText = false): string|array
    {
        $className = $this->getData("global." . $id . ".class");
        if ($this->hasData("global." . $id)) {
            $data = $this->getData("global." . $id);
            $value = $this->getValue($data);
        } else {
            $value = $id;
        }
        if (is_string($value) && $useText !== true && $className != "") {
            if ($useText === null && $value !== "") { // Use both icon and text
                AppendClass($className, "me-2");
            }
            if (preg_match('/\bspinner\b/', $className)) { // Spinner
                $res = '<div class="' . $className . '" role="status"><span class="visually-hidden">' . $value . '</span></div>';
            } else { // Icon
                $res = '<i data-phrase="' . $id . '" class="' . $className . '"><span class="visually-hidden">' . $value . '</span></i>';
            }
            if ($useText === null && $value !== "") { // Use both icon and text
                $res .= $value;
            }
            return $res;
        }
        return $value;
    }

    // Set phrase
    public function setPhrase(string $id, string $value): void
    {
        $this->setPhraseAttr($id, "value", $value);
    }

    // Get project phrase
    public function projectPhrase(string $id): string
    {
        return $this->getData("project." . $id . ".value");
    }

    // Set project phrase
    public function setProjectPhrase(string $id, string $value): void
    {
        $this->setData("project." . $id . ".value", $value);
    }

    // Get menu phrase
    public function menuPhrase(string $menuId, string $id): string
    {
        return $this->getData("project.menu." . $menuId . "." . $id . ".value");
    }

    // Set menu phrase
    public function setMenuPhrase(string $menuId, string $id, string $value): void
    {
        $this->setData("project.menu." . $menuId . "." . $id . ".value", $value);
    }

    // Get table phrase
    public function tablePhrase(string $tblVar, string $id): string
    {
        return $this->getData("project.table." . $tblVar .  "." . $id . ".value");
    }

    // Set table phrase
    public function setTablePhrase(string $tblVar, string $id, string $value): void
    {
        $this->setData("project.table." . $tblVar .  "." . $id . ".value", $value);
    }

	// Begin of modification Displaying Breadcrumbs in All Pages, by Masino Sinaga, September 9, 2023   
    // Get breadcrumb phrase
    function breadcrumbPhrase($id) {
        return $this->getData("global.breadcrumb." . $id . ".value", "");
    }
    // Set breadcrumb phrase
    function setBreadcrumbPhrase($id, $Value) {
        $this->setData("global.breadcrumb." . $id . ".value", $value);
    }  
	// End of modification Displaying Breadcrumbs in All Pages, by Masino Sinaga, September 9, 2023

    // Get chart phrase
    public function chartPhrase(string $tblVar, string $chtVar, string $id): string
    {
        return $this->getData("project.table." . $tblVar .  ".chart." . $chtVar . "." . $id . ".value");
    }

    // Set chart phrase
    public function setChartPhrase(string $tblVar, string $chtVar, string $id, string $value): void
    {
        $this->setData("project.table." . $tblVar .  ".chart." . $chtVar . "." . $id . ".value", $value);
    }

    // Get field phrase
    public function fieldPhrase(string $tblVar, string $fldVar, string $id): string
    {
        return $this->getData("project.table." . $tblVar .  ".field." . $fldVar . "." . $id . ".value");
    }

    // Set field phrase
    public function setFieldPhrase(string $tblVar, string $fldVar, string $id, string $value): void
    {
        $this->setData("project.table." . $tblVar .  ".field." . $fldVar . "." . $id . ".value", $value);
    }

    // Get phrase attribute
    protected function phraseAttr(string $id, string $name): string
    {
        return $this->getData("global." . $id . "." . $name);
    }

    // Set phrase attribute
    protected function setPhraseAttr(string $id, string $name, string $value): void
    {
        $this->setData("global." . $id . "." . $name, $value);
    }

    // Get phrase class
    public function phraseClass(string $id): string
    {
        return $this->phraseAttr($id, "class");
    }

    // Set phrase attribute
    public function setPhraseClass(string $id, string $value): void
    {
        $this->setPhraseAttr($id, "class", $value);
    }

    // Output array as JSON
    public function arrayToJson(): string
    {
        $ar = $this->Data->get("global");
        $keys = array_keys($ar);
        $res = array_combine($keys, array_map(fn($id) => $this->phrase($id, true), $keys));
        return json_encode($res);
    }

    // Output phrases to client side as JSON
    public function toJson(): string
    {
        return "ew.language.phrases = " . $this->arrayToJson() . ";";
    }

    // Output languages as array
    protected function getLanguages(): array
    {
        global $LANGUAGES, $CurrentLanguage;
        $ar = [];
        if (is_array($LANGUAGES) && count($LANGUAGES) > 1) {
            $finder = new Finder();
            $finder->files()->in($this->LanguageFolder)->name(Config("LANGUAGES_FILE")); // Find languages.xml
            foreach ($finder as $file) {
                $data = $this->loadXml($file->getContents());
                foreach ($LANGUAGES as $langId) {
					/*
					*/

					// Begin of modification by Masino Sinaga, September 23, 2023
                    if ($langId == "id-ID")
						$desc = "Indonesia";
					elseif ($langId == "en-US")
						$desc = "English";
					else 
						$desc = $this->phrase($langId);

					//$lang = array_merge([ "id" => $langId ], $data->has("global." . strtolower($langId)) ? $data->get("global." . strtolower($langId)) : [ "desc" => ($desc)]);

					// $lang = array_merge([ "id" => $langId ], $data->has("global." . strtolower($langId)) ? $data->get("global." . strtolower($langId)) : [ "desc" => $this->phrase($langId) ]);
					$lang = array_merge(
                        ["id" => $langId, "selected" => $langId == $CurrentLanguage],
                        $data->has("global." . strtolower($langId)) ? $data->get("global." . strtolower($langId)) : ["desc" => $this->phrase($langId)]
                    );

					// End of modification by Masino Sinaga, September 23, 2023

                    //$lang["selected"] = $langId == $CurrentLanguage;
                    $ar[] = $lang;
                }
                break; // Only one file
            }
			$this->LanguageCount = count($LANGUAGES); // Added by Masino Sinaga, September 9, 2023
        }
        return $ar;
    }

    // Set template
    public function setTemplate(string $value): void
    {
        $this->Template = $value;
    }

    // Get template
    public function getTemplate(): string
    {
        global $basePath;
		$basePath = BasePath(true);
        if ($this->Template == "" && $this->LanguageCount > 1) { // only display the language selector if language count is greater than 1 (one), modified by Masino Sinaga, April 26, 2022
            if (SameText($this->Type, "LI")) { // LI template (for used with top Navbar)
                return '{{for languages}}<li class="nav-item"><a class="nav-link{{if selected}} active{{/if}} " data-ew-action="language" data-language="{{:id}}"><img src="'.$basePath.'assets/media/flags/{{:id}}.svg" width="24px" height="17px" alt="" /> <span class="ew-language-option">{{:desc}}</span></a></li>{{/for}}';
            } elseif (SameText($this->Type, "DROPDOWN")) { // DROPDOWN template (for used with top Navbar)
                return '<li class="nav-item ew-language-option dropdown" style="cursor: pointer;"><a class="nav-link" data-bs-toggle="dropdown">{{for languages}}{{if selected}}<img src="'.$basePath.'assets/media/flags/{{:id}}.svg" width="24px" height="17px"  alt="" />{{/if}}{{/for}}</a><div class="dropdown-menu dropdown-menu-end p0">{{for languages}}<a class="dropdown-item d-flex align-items-center{{if selected}} active{{/if}}" data-ew-action="language" data-language="{{:id}}"><img class="" src="'.$basePath.'assets/media/flags/{{:id}}.svg" width="24px" height="17px" alt="" />{{>desc}}</a>{{/for}}</div></li>';
            } elseif (SameText($this->Type, "SELECT")) { // SELECT template (NOT for used with top Navbar)
                if (Language()->phrase("dir") != "rtl") {
					return '<div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim dropdown-menu-top-unround kt-hidden-desktop" style="position: absolute;will-change: transform;top: 0px;left: 0px;transform: translate3d(64px, 75px, 0px);right: auto;"><ul class="kt-nav kt-margin-t-10 kt-margin-b-10 user-profile">{{for languages}}<li class="kt-nav__item {{if selected}} kt-nav__item--active{{/if}}"><a href="javascript:void(0);" class="kt-nav__link" onclick="ew.setLanguage(this);" data-language="{{:id}}"><span class="kt-header__topbar-icon"><img class="" src="'.$basePath.'assets/media/flags/{{:id}}.svg" alt="" /></span>&nbsp;<span class="kt-nav__link-text"> {{:desc}}</span></a>{{/for}}</li></ul></div>
					<div class="ew-language-option kt-margin-t-20 kt-margin-b-10 user-profile kt-hidden-tablet-and-mobile" style="vertical-align:middle;"><select class="form-control" id="ew-language" name="ew-language" onchange="ew.setLanguage(this);" style="width:150px;">{{for languages}}<option value="{{:id}}"{{if selected}} selected{{/if}} data-image="'.$basePath.'plugins/language-selector-combobox/images/msdropdown/icons/blank.gif" data-imagecss="flag {{:id}}" data-title="{{:desc}}">&nbsp;{{:desc}}</option>{{/for}}</select></div>';
				} else {
					return '<div class="ew-language-option"><select class="form-control" id="ew-language" name="ew-language" onchange="ew.setLanguage(this);" style="width:150px;">{{for languages}}<option value="{{:id}}"{{if selected}} selected{{/if}} data-image="'.$basePath.'plugins/language-selector-combobox/images/msdropdown/icons/blank.gif" data-imagecss="flag {{:id}}" data-title="{{:desc}}">&nbsp;&nbsp;{{:desc}}</option>{{/for}}</select></div>';
				}
            } elseif (SameText($this->Type, "RADIO")) { // RADIO template (NOT for used with top Navbar)
                return '<div class="ew-language-option"><div class="btn-group" data-bs-toggle="buttons">{{for languages}}<input type="radio" name="ew-language" id="ew-Language-{{:id}}" onclick="ew.setLanguage(this);" {{if selected}} checked{{/if}} value="{{:id}}"><span class="ew-tooltip" for="ew-language-{{:id}}" data-container="body" data-bs-placement="middle" title="{{>desc}}"> <img class="" src="'.$basePath.'assets/media/flags/{{:id}}.svg" width="24px" height="17px" alt="" /></span>{{/for}}</div></div>';
            }
        }
        return $this->Template;
    }
}
