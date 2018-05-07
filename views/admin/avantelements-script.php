<script type="text/javascript">
    var fields = [<?php echo $fields; ?>];

    jQuery(document).ready(function ()
    {
        for (i = 0; i < fields.length; i++)
        {
            var elementId = fields[i];
            jQuery('[id^=Elements-' + elementId + ']').autocomplete({
                source: '<?php echo url('/elements/suggest/'); ?>' + elementId,
                minLength: 2
            });
        }

        jQuery('#clone-button').appendTo('#edit');
    });
</script>
