<script type="text/javascript">
    jQuery(document).ready(function ()
    {
        jQuery('#Elements-39-0-text').autocomplete({
            source: '<?php echo url('/elements/suggest/39'); ?>',
            minLength: 2
        });

        jQuery('#Elements-41-0-text').autocomplete({
            source: '<?php echo url('/elements/suggest'); ?>',
            minLength: 2
        });

        jQuery('#clone-button').appendTo('#edit');
    });
</script>
