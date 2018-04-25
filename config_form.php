<?php
$view = get_view();

$displayOrderOption = ElementsOptions::getOptionTextForDisplayOrder();
$displayOrderOptionRows = max(3, count(explode(PHP_EOL, $displayOrderOption)));

$externalLinkOption = ElementsOptions::getOptionTextForEXternalLink();
$externalLinkOptionRows = max(3, count(explode(PHP_EOL, $externalLinkOption)));

$implicitLinkOption = ElementsOptions::getOptionTextForImplicitLink();
$implicitLinkOptionRows = max(3, count(explode(PHP_EOL, $implicitLinkOption)));

$addInputOption = ElementsOptions::getOptionTextForAddInput();
$addInputOptionRows = max(3, count(explode(PHP_EOL, $addInputOption)));

$htmlOption = ElementsOptions::getOptionTextForHtml();
$htmlOptionRows = max(3, count(explode(PHP_EOL, $htmlOption)));

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
        <?php echo $view->formTextarea(ElementsOptions::OPTION_DISPLAY_ORDER, $displayOrderOption, array('rows' => $displayOrderOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Implicit Link'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should link to items with the the same value."); ?></p>
        <?php echo $view->formTextarea(ElementsOptions::OPTION_IMPLICIT_LINK, $implicitLinkOption, array('rows' => $implicitLinkOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('External Link'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that link to external web resources."); ?></p>
        <?php echo $view->formTextarea(ElementsOptions::OPTION_EXTERNAL_LINK, $externalLinkOption, array('rows' => $externalLinkOptionRows)); ?>
    </div>
</div>

<h3>Admin Elements</h3>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Allow Add Input'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should show the Add Input button"); ?></p>
        <?php echo $view->formTextarea(ElementsOptions::OPTION_ADD_INPUT, $addInputOption, array('rows' => $addInputOptionRows)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Allow HTML'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Elements that should show the Use HTML checkbox"); ?></p>
        <?php echo $view->formTextarea(ElementsOptions::OPTION_HTML, $htmlOption, array('rows' => $htmlOptionRows)); ?>
    </div>
</div>

<p>
    An element listed in one of the Width fields will appear as single line text box. Elements displayed as dropdown lists by the SimpleVocab plugin are not affected by this configuration.
</p>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Width 70'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should display 70 px wide"); ?></p>
        <?php echo $view->formText('avantelements_width_70', get_option('avantelements_width_70')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Width 160'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should display 160 px wide"); ?></p>
        <?php echo $view->formText('avantelements_width_160', get_option('avantelements_width_160')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Width 250'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should display 250 px wide"); ?></p>
        <?php echo $view->formText('avantelements_width_250', get_option('avantelements_width_250')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label><?php echo __('Width 380'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should display 380 px wide"); ?></p>
        <?php echo $view->formText('avantelements_width_380', get_option('avantelements_width_380')); ?>
    </div>
</div>
