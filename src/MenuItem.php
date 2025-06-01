<?php

namespace PHPMaker2025\ucarsip;

/**
 * Menu item class
 */
class MenuItem
{
    public ?Menu $SubMenu = null; // Data type = Menu
    public string $Target = "";
	public string $Source = ""; // Modified by Masino Sinaga, September 13, 2023
	public string $OnClick = ""; // Modified by Masino Sinaga, September 13, 2023
	public bool $IsJsMenu = false; // Modified by Masino Sinaga, September 13, 2023
    public string $Href = ""; // Href attribute
    public bool $Active = false;
    public int $Level = 0;

    // Constructor
    public function __construct(
        public int $Id = -1,
        public readonly string $Name = "",
        public string $Text = "",
        public string $Url = "",
        public int $ParentId = -1,
		public string $src = "", // put in the 6th order, added by Masino Sinaga, September 13, 2023
        public bool $Allowed = true,
        public bool $IsHeader = false,
        public bool $IsCustomUrl = false,
        public string $Icon = "",
        public string $Label = "", // HTML (for vertical menu only)
        public bool $IsNavbarItem = false,
        public bool $IsSidebarItem = false,
        public Attributes $Attrs = new Attributes() // HTML attributes
    ) {
		// Begin of modification by Masino Sinaga, April 23, 2012, in order to support _blank target in URL if it contains the prefix http
		$this->Source = $src;
		if (strpos($this->Url, "http://") !== false) {
		   $this->Target = "_blank";
		}
		// End of modification by Masino Sinaga, April 23, 2012, in order to support _blank target in URL if it contains the prefix http
		// Begin of modification by Masino Sinaga, June 3, 2014, in order to support onclick in URL if it contains the separator |||
		if (strpos($this->Url, "|||") !== false) {
		   list($this->Url, $this->OnClick) = explode("|||", $this->Url);
		   $this->IsJsMenu = true;
		}
		// End of modification by Masino Sinaga, June 3, 2014, in order to support onclick in URL if it contains the separator |||
    }

    // Set property case-insensitively (for backward compatibility) // PHP
    public function __set(string $name, mixed $value): void
    {
        $vars = get_class_vars($this::class);
        foreach ($vars as $key => $val) {
            if (SameText($name, $key)) {
                $this->$key = $value;
                break;
            }
        }
    }

    // Get property case-insensitively (for backward compatibility) // PHP
    public function __get(string $name): mixed
    {
        $vars = get_class_vars($this::class);
        foreach ($vars as $key => $val) {
            if (SameText($name, $key)) {
                return $this->$key;
                break;
            }
        }
        return null;
    }

    // Add submenu item
    public function addItem(MenuItem $item): void
    {
        if ($this->SubMenu === null) {
            $this->SubMenu = new Menu($this->Id);
        }
        $this->SubMenu->Level = $this->Level + 1;
        $this->SubMenu->addItem($item);
    }

    // Set attribute
    public function setAttribute(string $name, mixed $value): void
    {
        if (is_string($this->Attrs) && !preg_match('/\b' . preg_quote($name, '/') . '\s*=/', $this->Attrs)) { // Only set if attribute does not already exist
            $this->Attrs .= ' ' . $name . '="' . $value . '"';
        } elseif ($this->Attrs instanceof Attributes) {
            if (StartsText("on", $name)) { // Events
                $this->Attrs->append($name, $value, ";");
            } elseif (SameText("class", $name)) { // Class
                $this->Attrs->appendClass($value);
            } else {
                $this->Attrs->append($name, $value);
            }
        }
    }

    // Render
    public function render(bool $deep = true): array
    {
        $url = GetUrl($this->Url);
        if (IsMobile() && !$this->IsCustomUrl && $url != "#") {
            $url = str_replace("#", (ContainsString($url, "?") ? "&" : "?") . "hash=", $url);
        }
        if ($url == "") {
            $this->setAttribute("data-ew-action", "none");
        }
        $icon = trim($this->Icon);
        if ($icon && ContainsString($icon, "fa-")) {
            $ar = ClassList($icon);
            if (count(array_intersect($ar, ["fa-solid", "fa-regular", "fa-light", "fa-thin", "fa-duotone", "fa-sharp", "fa-brands"])) == 0) {
                $ar[] = "fa-solid";
            }
            $icon = implode(" ", $ar);
        }
        $hasItems = $deep && $this->SubMenu !== null;
        $isOpened = $hasItems && $this->SubMenu->isOpened();
        $class = "";
        if ($this->IsNavbarItem) {
            AppendClass($class, SameString($this->ParentId, "-1") || $this->IsSidebarItem ? "nav-link" : "dropdown-item");
            if ($this->Active) {
                AppendClass($class, "active");
            }
            if ($hasItems && !$this->IsSidebarItem) {
                AppendClass($class, "dropdown-toggle ew-dropdown");
            }
        } else {
            AppendClass($class, "nav-link");
            if ($this->Active || $isOpened) {
                AppendClass($class, "active");
            }
        }
        AppendClass($class, @$this->Attrs["class"]); // Move all user classes at end
        $this->Attrs["class"] = $class; // Save classes to Attrs
        $attrs = is_string($this->Attrs) ? $this->Attrs : $this->Attrs->toString();
        return [
            "id" => $this->Id,
            "name" => $this->Name,
            "text" => $this->Text,
            "parentId" => $this->ParentId,
            "level" => $this->Level,
            "href" => $url,
			"source" => $this->Source, // added by Masino Sinaga, October 13, 2024
            "attrs" => $attrs,
            "target" => $this->Target,
            "isHeader" => $this->IsHeader,
            "active" => $this->Active,
            "icon" => $icon,
            "label" => $this->Label,
            "isNavbarItem" => $this->IsNavbarItem,
            "items" => $hasItems ? $this->SubMenu->render() : null,
            "open" => $isOpened
        ];
    }
}
