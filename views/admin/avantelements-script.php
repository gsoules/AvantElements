<script type="text/javascript">
    var suggestRelationshipUrl = '<?php echo url('/elements/suggest'); ?>';

    function afterSuggestValue(inputId, data)
    {
        var input = jQuery(inputId);
        var select = jQuery(data.html);
        select.insertAfter(input);
        select.on('click', function(event)
        {
            onClickSuggestion(inputId, select.attr('id'))
        });
    }

    function onClickSuggestion(inputId, selectId)
    {
        $selection = jQuery('#' + selectId + ' option:selected').text();
        jQuery(inputId).val($selection);
    }

    function initializeSuggestValue()
    {
        var suggestButton = jQuery('.suggest-button');

        suggestButton.click(function (event)
        {
            event.preventDefault();
            var fieldId = jQuery(this).parents('.field').attr('id');
            suggestValue(fieldId);
        });

//        jQuery('.add-element').click(function (event)
//        {
//            event.preventDefault();
//            suggestButton.hide();
//        });
    }

    function suggestValue(fieldId)
    {
        var elementId = fieldId.substring(8);

        jQuery('#suggest-' + elementId).remove();

        var inputId = '#Elements-' + elementId + '-0-text';
        var input = jQuery(inputId);
        var keywords = input.val();
        var suggestButton = jQuery('#' + fieldId).find('.suggest-button');
        suggestButton.text('<?php echo __('Searching...'); ?>');

        jQuery.ajax(
            suggestRelationshipUrl,
            {
                method: 'POST',
                dataType: 'json',
                data: {
                    action: <?php echo ElementSuggest::SUGGEST; ?>,
                    elementId: elementId,
                    keywords: keywords
                },
                success: function (data) {
                    suggestButton.text('<?php echo __('Suggest'); ?>');
                    afterSuggestValue(inputId, data);
                },
                error: function (data) {
                    alert('AJAX Error on Suggest: ' + data.statusText + ' : ' + data.responseText);
                }
            }
        );
    }

    jQuery(document).ready(function ()
    {
        initializeSuggestValue();

        jQuery('#clone-button').appendTo('#edit');
    });
</script>
