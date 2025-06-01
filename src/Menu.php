<?php

namespace PHPMaker2025\ucarsip;

/**
 * Menu class
 */
class Menu
{
    public bool $Accordion = true; // For sidebar menu only
    public bool $Compact = false; // For sidebar menu only
    public bool $UseSubmenu = false;
    public array $Items = [];
    public int $Level = 0;

    // Constructor
    public function __construct(
        public string $Id,
        public bool $IsRoot = false,
        public bool $IsNavbar = false
    ) {
        if ($this->IsNavbar) {
            $this->UseSubmenu = true;
            $this->Accordion = false;
        }
    }

    // Add a menu item ($src for backward compatibility only)
    public function addMenuItem(
        int $id,
        string $name,
        string $text,
        string $url,
        int $parentId = -1,
        string $src = "",
        bool $allowed = true,
        bool $isHeader = false,
        bool $isCustomUrl = false,
        string $icon = "",
        string $label = "",
        bool $isNavbarItem = false,
        bool $isSidebarItem = false): void
    {
        $item = new MenuItem($id, $name, $text, $url, $parentId, $src, $allowed, $isHeader, $isCustomUrl, $icon, $label, $isNavbarItem, $isSidebarItem); // added $src as the 6th param, by Masino Sinaga, September 13, 2023

        // MenuItem_Adding event
        DispatchEvent(new MenuItemAddingEvent($item, $this), MenuItemAddingEvent::NAME);
        if (!$item->Allowed) {
            return;
        }
        if ($item->ParentId < 0) {
            $this->addItem($item);
        } elseif ($parentMenu = $this->findItem($item->ParentId)) {
            $parentMenu->addItem($item);
        }

        // Set item active
        if (!$item->IsCustomUrl && CurrentPageName() == GetPageName($item->Url) || $item->IsCustomUrl && $item->Url != "" && CurrentUrl() == GetUrl($item->Url) || $item->Source == CurrentTable()?->TableName) { // Active, modified by Masino Sinaga, September 13 ,2023 by adding this:  || $item->Source == CurrentPage()->TableName
            $item->Active = true;
        }
    }

    // Add item to internal array
    public function addItem(MenuItem $item): void
    {
        $item->Level = $this->Level;
        $this->Items[] = $item;
    }

    // Clear all menu items
    public function clear(): void
    {
        $this->Items = [];
    }

    // Find item
    public function findItem(int $id): ?MenuItem
    {
        foreach ($this->Items as $item) {
            if ($item->Id == $id) {
                return $item;
            } elseif ($subitem = $item->SubMenu?->findItem($id)) {
                return $subitem;
            }
        }
        return null;
    }

    // Find item by menu text
    public function findItemByText(string $txt): ?MenuItem
    {
        foreach ($this->Items as $item) {
            if ($item->Text == $txt) {
                return $item;
            } elseif ($subitem = $item->SubMenu?->findItemByText($txt)) {
                return $subitem;
            }
        }
        return null;
    }

    // Get menu item count
    public function count(): int
    {
        return count($this->Items);
    }

    // Move item to position
    public function moveItem(string $text, int $pos): void
    {
        $cnt = count($this->Items);
        if ($pos < 0) {
            $pos = 0;
        } elseif ($pos >= $cnt) {
            $pos = $cnt - 1;
        }
        $item = null;
        $cnt = count($this->Items);
        for ($i = 0; $i < $cnt; $i++) {
            if ($this->Items[$i]->Text == $text) {
                $item = $this->Items[$i];
                break;
            }
        }
        if ($item) {
            unset($this->Items[$i]);
            $this->Items = array_merge(
                array_slice($this->Items, 0, $pos),
                [$item],
                array_slice($this->Items, $pos)
            );
        }
    }

    // Check if a menu item should be shown
    public function renderItem(MenuItem $item): bool
    {
        if ($item->SubMenu != null) {
            return array_any($item->SubMenu->Items, fn($subitem) => $item->SubMenu->renderItem($subitem));
        }
        return $item->Allowed && $item->Url != "";
    }

    // Check if a menu item should be opened
    public function isItemOpened(MenuItem $item): bool
    {
		/* Begin of modification by Masino Sinaga, December 1, 2024
        if ($item->SubMenu != null) {
            return array_any($item->SubMenu->Items, fn($subitem) => $item->SubMenu->isItemOpened($subitem));
        }
        return $item->Active;
		*/
		// Do not use the code above, since it will not expand multiple parent menu items!
		// Use the code below instead!!!
		if ($item->SubMenu != null) {
            foreach ($item->SubMenu->Items as $subitem) {
                if ($item->SubMenu->isItemOpened($subitem)) {
                    return true;
                }
            }
        }
        return $item->Active;
		// End of modification by Masino Sinaga, December 1, 2024
    }

    // Check if this menu should be rendered
    public function renderMenu(): bool
    {
        return array_any($this->Items, fn($item) => $this->renderItem($item));
    }

    // Check if this menu should be opened
    public function isOpened(): bool
    {
        return array_any($this->Items, fn($item) => $this->isItemOpened($item));
    }

    // Render the menu as array of object
    public function render(): ?array
    {
        if ($this->IsRoot) {
            DispatchEvent(new MenuRenderingEvent($this), MenuRenderingEvent::NAME);
        }
        if (!$this->renderMenu()) {
            return null;
        }
        $menu = [];
        $url = CurrentUrl();
        $checkUrl = function ($item) use ($url) {
            //if (!$item->IsCustomUrl && CurrentPageName() == GetPageName($item->Url) || $item->IsCustomUrl && $url == GetUrl($item->Url)) { // Active <-- original code!
			if (!$item->IsCustomUrl && CurrentPageName() == GetPageName($item->Url) || $item->IsCustomUrl && CurrentUrl() == GetUrl($item->Url) || $item->IsCustomUrl && CurrentPageName() == GetPageName($item->Url)) { // Active <-- Modified by Masino Sinaga, September 13, 2023 //  || $item->Source == CurrentPage()->PageObjName
				// Begin of modification Masino Sinaga, September 13, 2023
				// $item->Active = true; <-- if used, then when 404 error happened, all parent menu item will be expanded!
				if ($item->IsJsMenu == true) {
					$item->setAttribute("onclick=", $item->OnClick);
				}
				$item->Url = "javascript:void(0);";
				$item->setAttribute("onclick", "return false;");
				// End of modification Masino Sinaga, September 13, 2023
                $item->setAttribute("data-ew-action", "none");
            } elseif ($item->SubMenu != null && $item->Url != "#" && $this->IsNavbar && $this->IsRoot) { // Navbar root menu item with submenu
                // $item->Attrs["data-url"] = $item->Url; <-- original code!
				// Begin of modification Masino Sinaga, September 13, 2023
				$item->setAttribute("data-url", $item->Url);
				if ($item->IsJsMenu == true) {
					$item->setAttribute("data-url", $item->Url);
					$item->setAttribute("onclick", $item->OnClick);
				} else {
					$item->setAttribute("data-url", $item->Url);
				}
				$item->Url = "javascript:void(0);"; // Does not support URL for root menu item with submenu
				$item->setAttribute("onclick", "return false;");
				// End of modification Masino Sinaga, September 13, 2023
                $item->setAttribute("data-ew-action", "none");
            } else  { // Begin of modification Masino Sinaga, September 13, 2023
				if ($item->IsJsMenu == true) {
					$item->setAttribute("onclick", $item->OnClick);
				}
            } // End of modification Masino Sinaga, September 13, 2023
        };
        foreach ($this->Items as $item) {
            if ($this->renderItem($item)) {
                if ($item->IsHeader && (!$this->IsRoot || !$this->UseSubmenu)) { // Group title (Header)
                    $checkUrl($item);
                    $menu[] = $item->render(false);
                    if ($item->SubMenu != null) {
                        foreach ($item->SubMenu->Items as $subitem) {
                            if ($this->renderItem($subitem)) {
                                $checkUrl($subitem);
                                $menu[] = $subitem->render();
                            }
                        }
                    }
                } else {
                    $checkUrl($item);
                    $menu[] = $item->render();
                }
            }
        }
        if ($this->IsRoot) {
            DispatchEvent(new MenuRenderedEvent($this), MenuRenderedEvent::NAME);
        }
        return count($menu) ? $menu : null;
    }

    // Returns the menu as JSON
    public function toJson(): string|bool
    {
        return json_encode(["items" => $this->render(), "accordion" => $this->Accordion, "compact" => $this->Compact]);
    }

    // Returns the menu as script tag
    public function toScript(): string
    {
        return "<script" . Nonce() . ">ew.vars.{$this->Id} = {$this->toJson()};</script>";
    }
}
