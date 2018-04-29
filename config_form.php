<?php
$view = get_view();

$displayOrderOption = ElementsConfig::getOptionTextForDisplayOrder();
$displayOrderOptionRows = max(3, count(explode(PHP_EOL, $displayOrderOption)));

$externalLinkOption = ElementsConfig::getOptionTextForExternalLink();
$externalLinkOptionRows = max(3, count(explode(PHP_EOL, $externalLinkOption)));

$implicitLinkOption = ElementsConfig::getOptionTextForImplicitLink();
$implicitLinkOptionRows = max(3, count(explode(PHP_EOL, $implicitLinkOption)));

$titleSyncOption = ElementsConfig::getOptionTextForTitleSync();
$titleSyncOptionRows = max(3, count(explode(PHP_EOL, $titleSyncOption)));

$validationOption = ElementsConfig::getOptionTextForValidation();
$validationOptionRows = max(3, count(explode(PHP_EOL, $validationOption)));

$addInputOption = ElementsConfig::getOptionTextForAddInput();
$addInputOptionRows = max(3, count(explode(PHP_EOL, $addInputOption)));

$htmlOption = ElementsConfig::getOptionTextForHtml();
$htmlOptionRows = max(3, count(explode(PHP_EOL, $htmlOption)));

$textField = ElementsConfig::getOptionTextForTextField();
$textFieldRows = max(3, count(explode(PHP_EOL, $textField)));

$customFilterOption = ElementsConfig::getOptionTextForCallback();
$customFilterOptionRows = max(3, count(explode(PHP_EOL, $customFilterOption)));

?>
<style>
    .error{color:red;font-size:16px;}
</style>

<div class="plugin-help learn-more">
    <a href="https://github.com/gsoules/AvantElements#usage" target="_blank">Learn about the configuration options on this page</a>
</div>

<h3>Public Elements</h3>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Display Order'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The order to display elements on public Show pages."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_DISPLAY_ORDER, $displayOrderOption, array('rows' => $displayOrderOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Implicit Link'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should link to items with the the same value."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_IMPLICIT_LINK, $implicitLinkOption, array('rows' => $implicitLinkOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('External Link'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that link to external web resources."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_EXTERNAL_LINK, $externalLinkOption, array('rows' => $externalLinkOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Title Sync'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should stay in sync with corrresponding titles."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_TITLE_SYNC, $titleSyncOption, array('rows' => $titleSyncOptionRows)); ?>
    </div>
</div>

<h3>Admin Elements</h3>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Validation'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that must be validated."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_VALIDATION, $validationOption, array('rows' => $validationOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Callback Function'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Callback functions to be called for individual elements."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_CALLBACK, $customFilterOption, array('rows' => $customFilterOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Allow Add Input'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that show the Add Input button."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_ADD_INPUT, $addInputOption, array('rows' => $addInputOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Allow HTML'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that show the Use HTML checkbox."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_HTML, $htmlOption, array('rows' => $htmlOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Text Field'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should display as a text field."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_TEXT_FIELD, $textField, array('rows' => $textFieldRows)); ?>
    </div>
</div>
