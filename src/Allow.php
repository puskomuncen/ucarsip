<?php

namespace PHPMaker2025\ucarsip;

// Allow
enum Allow: int
{
    case ADD = 1;
    case DELETE = 2;
    case EDIT = 4;
    case LIST = 8;
    case ACCESS = 16;
    case VIEW = 32;
    case SEARCH = 64;
    case IMPORT = 128;
    case LOOKUP = 256;
    case PUSH = 512;
    case EXPORT = 1024;
    case GRANT = 2048;
    case PRINT = 4096;
    case EXCEL = 8192;
    case WORD = 16384;
    case HTML = 32768;
    case XML = 65536;
    case CSV = 131072;
    case PDF = 262144;
    case EMAIL = 524288;
    case ALL_NEW = 1048575;
    case ADMIN = 16777215;

    public static function privileges(): array
    {
        $arr = array_change_key_case(array_column(self::cases(), "value", "name"));
        unset($arr["legacy_admin"]);
        return $arr;
    }
}
