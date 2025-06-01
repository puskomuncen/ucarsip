<?php

namespace PHPMaker2025\ucarsip;

/**
 * Sub page class
 */
class SubPage
{
    public bool $Active = false;
    public bool $Visible = true; // If false, add class "d-none", for tabs/pills only
    public bool $Disabled = false; // If true, add class "disabled", for tabs/pills only
}
