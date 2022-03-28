<script type="text/javascript">
    var fields = [<?php echo $fields; ?>];
    var identifier = "<?php echo $identifier; ?>";

    // Enable auto-complete for each instance of each element having an Id in the fields list.
    // If the user has clicked the Add Input button, the form will reload and replace the existing
    // inputs for an element with those plus the new one. This function adds auto-complete to each.
    function enableAutoComplete()
    {
        for (i = 0; i < fields.length; i++) {
            var elementId = fields[i];
            jQuery('[id^=Elements-' + elementId + ']').each(function () {
                jQuery(this).autocomplete({
                    source: '<?php echo url('/elements/suggest/'); ?>' + elementId,
                    minLength: 2
                });
            });
        }
    }

    function enableSearchableSelect()
    {
        // Initialize the searchable select dropdown from https://select2.org.
        jQuery('.avantelements-select').select2();
    }

    // Handle both an initial form load, and when the form is updated after the Add Input button is clicked.
    jQuery(document).bind('omeka:elementformload', function (event) {
        enableAutoComplete();
        enableSearchableSelect();
    });

    // Move the Duplicate Item button to the end of the set of other buttons.
    jQuery('#clone-button').appendTo('#edit');

    // On the Edit Item page, replace the Omeka message that shows the item Id, with one that shows the Identifier.
    jQuery('.items.edit h1#content-heading.section-title').text('Edit Item ' + identifier);
</script>
