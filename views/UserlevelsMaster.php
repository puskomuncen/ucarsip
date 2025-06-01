<?php

namespace PHPMaker2025\ucarsip;

// Table
$userlevels = Container("userlevels");
$userlevels->TableClass = "table table-bordered table-hover table-sm ew-table ew-master-table";
?>
<?php if ($userlevels->Visible) { ?>
<div class="ew-master-div">
<table id="tbl_userlevelsmaster" class="table ew-view-table ew-master-table ew-vertical">
    <tbody>
<?php if ($userlevels->ID->Visible) { // ID ?>
        <tr id="r_ID"<?= $userlevels->ID->rowAttributes() ?>>
            <td class="<?= $userlevels->TableLeftColumnClass ?>"><?= $userlevels->ID->caption() ?></td>
            <td<?= $userlevels->ID->cellAttributes() ?>>
<span id="el_userlevels_ID">
<span<?= $userlevels->ID->viewAttributes() ?>>
<?= $userlevels->ID->getViewValue() ?></span>
</span>
</td>
        </tr>
<?php } ?>
<?php if ($userlevels->Name->Visible) { // Name ?>
        <tr id="r_Name"<?= $userlevels->Name->rowAttributes() ?>>
            <td class="<?= $userlevels->TableLeftColumnClass ?>"><?= $userlevels->Name->caption() ?></td>
            <td<?= $userlevels->Name->cellAttributes() ?>>
<span id="el_userlevels_Name">
<span<?= $userlevels->Name->viewAttributes() ?>>
<?= $userlevels->Name->getViewValue() ?></span>
</span>
</td>
        </tr>
<?php } ?>
<?php if ($userlevels->Hierarchy->Visible) { // Hierarchy ?>
        <tr id="r_Hierarchy"<?= $userlevels->Hierarchy->rowAttributes() ?>>
            <td class="<?= $userlevels->TableLeftColumnClass ?>"><?= $userlevels->Hierarchy->caption() ?></td>
            <td<?= $userlevels->Hierarchy->cellAttributes() ?>>
<span id="el_userlevels_Hierarchy">
<span<?= $userlevels->Hierarchy->viewAttributes() ?>>
<?= $userlevels->Hierarchy->getViewValue() ?></span>
</span>
</td>
        </tr>
<?php } ?>
<?php if ($userlevels->Level_Origin->Visible) { // Level_Origin ?>
        <tr id="r_Level_Origin"<?= $userlevels->Level_Origin->rowAttributes() ?>>
            <td class="<?= $userlevels->TableLeftColumnClass ?>"><?= $userlevels->Level_Origin->caption() ?></td>
            <td<?= $userlevels->Level_Origin->cellAttributes() ?>>
<span id="el_userlevels_Level_Origin">
<span<?= $userlevels->Level_Origin->viewAttributes() ?>>
<?= $userlevels->Level_Origin->getViewValue() ?></span>
</span>
</td>
        </tr>
<?php } ?>
    </tbody>
</table>
</div>
<?php } ?>
