<script type="text/javascript">
    var fields = [<?php echo $fields; ?>];

    function enableAutoComplete()
    {
        for (i = 0; i < fields.length; i++) {
            var elementId = fields[i];
            jQuery('[id^=Elements-' + elementId + ']').each(function () {
                var d = new Date();
                var n = d.getTime();
                console.log(n + ' ' + this.id);
                jQuery(this).autocomplete({
                    source: '<?php echo url('/elements/suggest/'); ?>' + elementId,
                    minLength: 2
                });
            });
        }
    }

    jQuery(document).bind('omeka:elementformload', function (event) {
        console.log("LOAD");
        enableAutoComplete();
    });


    jQuery('#clone-button').appendTo('#edit');
</script>
