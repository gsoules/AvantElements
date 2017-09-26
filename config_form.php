<?php $view = get_view(); ?>

<p>
    All fields below take a comma-separated list of element names. A space after the comma is allowed, but optional.
</p>

<div class="field">
    <div class="two columns alpha">
        <label for="configure_elements_display_order"><?php echo __('Display Order'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of element names in the order they should appear on the public Show page"); ?></p>
        <?php echo $view->formTextarea('configure_elements_display_order', get_option('configure_elements_display_order')); ?>
    </div>
</div>

<p>
    Any element not specified below will appear as a multi-line text box with no Add Input button and no Use HTML
    checkbox.
</p>

<div class="field">
    <div class="two columns alpha">
        <label for="configure_elements_allow_add_input"><?php echo __('Allow Add Input'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should show the Add Input button"); ?></p>
        <?php echo $view->formText('configure_elements_allow_add_input', get_option('configure_elements_allow_add_input')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="configure_elements_allow_html"><?php echo __('Allow HTML'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should show the Use HTML checkbox"); ?></p>
        <?php echo $view->formText('configure_elements_allow_html', get_option('configure_elements_allow_html')); ?>
    </div>
</div>

<p>
    An element listed in one of the Width fields will appear as single line text box. Elements displayed as dropdown lists by the SimpleVocab plugin are not affected by this configuration.
</p>

<div class="field">
    <div class="two columns alpha">
        <label for="configure_elements_width_70"><?php echo __('Width 70'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should display 70 px wide"); ?></p>
        <?php echo $view->formText('configure_elements_width_70', get_option('configure_elements_width_70')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="configure_elements_width_160"><?php echo __('Width 160'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should display 160 px wide"); ?></p>
        <?php echo $view->formText('configure_elements_width_160', get_option('configure_elements_width_160')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="configure_elements_width_250"><?php echo __('Width 250'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should display 250 px wide"); ?></p>
        <?php echo $view->formText('configure_elements_width_250', get_option('configure_elements_width_250')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="configure_elements_width_380"><?php echo __('Width 380'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of elements that should display 380 px wide"); ?></p>
        <?php echo $view->formText('configure_elements_width_380', get_option('configure_elements_width_380')); ?>
    </div>
</div>
