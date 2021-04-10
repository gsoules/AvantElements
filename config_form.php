<?php
$view = get_view();

$displayOrderOption = ElementsConfig::getOptionTextForDisplayOrder();
$displayOrderOptionRows = max(2, count(explode(PHP_EOL, $displayOrderOption)));

$implicitLinkOption = ElementsConfig::getOptionTextForImplicitLink();
$implicitLinkOptionRows = max(2, count(explode(PHP_EOL, $implicitLinkOption)));

$externalLinkOption = ElementsConfig::getOptionTextForExternalLink();
$externalLinkOptionRows = max(2, count(explode(PHP_EOL, $externalLinkOption)));

$validationOption = ElementsConfig::getOptionTextForValidation();
$validationOptionRows = max(2, count(explode(PHP_EOL, $validationOption)));

$hideCommentOption = ElementsConfig::getOptionTextForHideComment();
$hideCommentOptionRows = max(2, count(explode(PHP_EOL, $hideCommentOption)));

$hideDescriptionOption = ElementsConfig::getOptionTextForHideDescription();
$hideDescriptionOptionRows = max(2, count(explode(PHP_EOL, $hideDescriptionOption)));

$addInputOption = ElementsConfig::getOptionTextForAddInput();
$addInputOptionRows = max(2, count(explode(PHP_EOL, $addInputOption)));

$htmlOption = ElementsConfig::getOptionTextForHtml();
$htmlOptionRows = max(2, count(explode(PHP_EOL, $htmlOption)));

$placeholderOption = ElementsConfig::getOptionTextForPlaceholder();
$placeholderOptionRows = max(2, count(explode(PHP_EOL, $placeholderOption)));

$textareaRows = ElementsConfig::getOptionTextForTextareaRows();

$textField = ElementsConfig::getOptionTextForTextField();
$textFieldRows = max(2, count(explode(PHP_EOL, $textField)));

$selectField = ElementsConfig::getOptionTextForSelectField();
$selectFieldRows = max(2, count(explode(PHP_EOL, $selectField)));

$checkboxField = ElementsConfig::getOptionTextForCheckboxField();
$checkboxFieldRows = max(2, count(explode(PHP_EOL, $checkboxField)));

$readonlyOption = ElementsConfig::getOptionTextForReadonlyField();
$readonlyOptionRows = max(2, count(explode(PHP_EOL, $readonlyOption)));

$defaultValueOption = ElementsConfig::getOptionTextForDefaultValue();
$defaultValueOptionRows = max(2, count(explode(PHP_EOL, $defaultValueOption)));

$suggestOption = ElementsConfig::getOptionTextForSuggest();
$suggestOptionRows = max(2, count(explode(PHP_EOL, $suggestOption)));

$titleSyncOption = ElementsConfig::getOptionTextForTitleSync();
$titleSyncOptionRows = max(2, count(explode(PHP_EOL, $titleSyncOption)));

$callbackOption = ElementsConfig::getOptionTextForCallback();
$callbackOptionRows = max(2, count(explode(PHP_EOL, $callbackOption)));

?>
<style>
    .error{color:red;font-size:16px;}
</style>

<div class="plugin-help learn-more">
   <a href="https://digitalarchive.us/plugins/avantelements/" target="_blank">Learn about the configuration options on this page</a>
</div>

<h3>Public Elements</h3>

<div class="field">
   <div class="two columns alpha">
       <label><?php echo CONFIG_LABEL_DISPLAY_ORDER; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("The order to display elements on public Show pages."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_DISPLAY_ORDER, $displayOrderOption, array('rows' => $displayOrderOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_IMPLICIT_LINK; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should link to items with the same value."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_IMPLICIT_LINK, $implicitLinkOption, array('rows' => $implicitLinkOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_EXTERNAL_LINK; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that link to external web resources."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_EXTERNAL_LINK, $externalLinkOption, array('rows' => $externalLinkOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_EXTERNAL_LINK_ICON; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Add icon to external links.'); ?></p>
        <?php echo $view->formCheckbox(ElementsConfig::OPTION_EXTERNAL_LINK_ICON, true, array('checked' => (boolean)get_option(ElementsConfig::OPTION_EXTERNAL_LINK_ICON))); ?>
    </div>
</div>

<h3>Admin Elements</h3>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_HIDE_DESCRIPTION; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements whose description must be hidden."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_HIDE_DESCRIPTION, $hideDescriptionOption, array('rows' => $hideDescriptionOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_HIDE_COMMENT; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements whose comment must be hidden."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_HIDE_COMMENT, $hideCommentOption, array('rows' => $hideCommentOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_VALIDATION; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that must be validated."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_VALIDATION, $validationOption, array('rows' => $validationOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_ADD_INPUT; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that show the Add Input button."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_ADD_INPUT, $addInputOption, array('rows' => $addInputOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_HTML; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that show the Use HTML checkbox."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_HTML, $htmlOption, array('rows' => $htmlOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_PLACEHOLDER; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that show a placeholder."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_PLACEHOLDER, $placeholderOption, array('rows' => $placeholderOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_TEXTAREA_ROWS; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Number of rows for textarea fields."); ?></p>
        <?php echo $view->formText(ElementsConfig::OPTION_TEXTAREA_ROWS, $textareaRows); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_TEXT_FIELD; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should display as a text field."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_TEXT_FIELD, $textField, array('rows' => $textFieldRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_SELECT_FIELD; ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php if (plugin_is_active('SimpleVocab') || plugin_is_active('AvantVocabulary')): ?>
            <p class="explanation"><?php echo __("Vocabulary elements that display as a dropdown list."); ?></p>
            <?php echo $view->formTextarea(ElementsConfig::OPTION_SELECT_FIELD, $selectField, array('rows' => $selectFieldRows)); ?>
        <?php else: ?>
            <?php ElementsConfig::emitOptionNotSupported('AvantElements', 'vocabulary-field-option'); ?>
        <?php endif; ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_CHECKBOX_FIELD; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should display as a checkbox."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_CHECKBOX_FIELD, $checkboxField, array('rows' => $checkboxFieldRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_READONLY_FIELD; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should display as read-only."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_READONLY_FIELD, $readonlyOption, array('rows' => $readonlyOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_DEFAULT_VALUE; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Default values to use when adding a new Item."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_DEFAULT_VALUE, $defaultValueOption, array('rows' => $defaultValueOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_SUGGEST; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should offer input suggestions."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_SUGGEST, $suggestOption, array('rows' => $suggestOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_TITLE_SYNC; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should stay in sync with corrresponding titles."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_TITLE_SYNC, $titleSyncOption, array('rows' => $titleSyncOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo CONFIG_LABEL_CALLBACK; ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Callback functions to be called for individual elements."); ?></p>
        <?php echo $view->formTextarea(ElementsConfig::OPTION_CALLBACK, $callbackOption, array('rows' => $callbackOptionRows)); ?>
    </div>
</div>
