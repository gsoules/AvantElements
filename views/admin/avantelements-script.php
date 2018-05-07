<script type="text/javascript">
    jQuery(document).ready(function ()
    {
        for (i=39; i<=41; i++)
        {
            jQuery('#Elements-' + i + '-0-text').autocomplete({
                source: '<?php echo url('/elements/suggest/'); ?>' + i,
                minLength: 2
            });
        }


        //jQuery('#Elements-41-0-text').autocomplete({
        //    source: '<?php //echo url('/elements/suggest'); ?>//',
        //    minLength: 2
        //});

        jQuery('#clone-button').appendTo('#edit');
    });
</script>
