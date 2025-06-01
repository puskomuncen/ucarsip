<?php

namespace PHPMaker2025\ucarsip;

// Table
$help_categories = Container("help_categories");
$help_categories->TableClass = "table table-bordered table-hover table-sm ew-table ew-master-table";
?>
<?php if ($help_categories->Visible) { ?>
<div class="ew-master-div">
<table id="tbl_help_categoriesmaster" class="table ew-view-table ew-master-table ew-vertical">
    <tbody>
<?php if ($help_categories->Category_ID->Visible) { // Category_ID ?>
        <tr id="r_Category_ID"<?= $help_categories->Category_ID->rowAttributes() ?>>
            <td class="<?= $help_categories->TableLeftColumnClass ?>"><?= $help_categories->Category_ID->caption() ?></td>
            <td<?= $help_categories->Category_ID->cellAttributes() ?>>
<span id="el_help_categories_Category_ID">
<span<?= $help_categories->Category_ID->viewAttributes() ?>>
<?= $help_categories->Category_ID->getViewValue() ?></span>
</span>
</td>
        </tr>
<?php } ?>
<?php if ($help_categories->_Language->Visible) { // Language ?>
        <tr id="r__Language"<?= $help_categories->_Language->rowAttributes() ?>>
            <td class="<?= $help_categories->TableLeftColumnClass ?>"><?= $help_categories->_Language->caption() ?></td>
            <td<?= $help_categories->_Language->cellAttributes() ?>>
<span id="el_help_categories__Language">
<span<?= $help_categories->_Language->viewAttributes() ?>>
<?= $help_categories->_Language->getViewValue() ?></span>
</span>
</td>
        </tr>
<?php } ?>
<?php if ($help_categories->Category_Description->Visible) { // Category_Description ?>
        <tr id="r_Category_Description"<?= $help_categories->Category_Description->rowAttributes() ?>>
            <td class="<?= $help_categories->TableLeftColumnClass ?>"><?= $help_categories->Category_Description->caption() ?></td>
            <td<?= $help_categories->Category_Description->cellAttributes() ?>>
<span id="el_help_categories_Category_Description">
<span<?= $help_categories->Category_Description->viewAttributes() ?>>
<?= $help_categories->Category_Description->getViewValue() ?></span>
</span>
</td>
        </tr>
<?php } ?>
    </tbody>
</table>
</div>
<?php } ?>
