<?php
class ElementFilters
{
    protected $addInputElements;
    protected $elementCloning;
    protected $htmlElements;
    protected $textFields;

    public function __construct()
    {
        $this->addInputElements = ElementsConfig::getOptionDataForAddInput();
        $this->textFields = ElementsConfig::getOptionDataForTextField();
        $this->htmlElements = ElementsConfig::getOptionDataForHtml();
        $this->elementCloning = new ElementCloning();
    }

    protected function createField(ElementValidator $elementValidator, $components, $args, $item, $elementId)
    {
        // This method determines if the element's Text Area needs to be modified or converted to a Text Box.
        // A Text Area is modified when it needs to display a default value for a new item. A TextArea is
        // converted to a Text Box when the element is specified as a Text Field on the configuration page.
        // This logic takes a brute force approach to performing these tasks. It's easier and safer to simply
        // replace the existing <input> HTML for the field than to parse and modify the existing HTML. The
        // HTML for a field is contained in the $components array's 'input' element.

        $hasDefaultValue = false;
        $value = $args['value'];

        if (empty($item->id) && empty($value))
        {
            // This is a new item and this element is blank. See if there is a default value.
            $value = $elementValidator->getCallbackDefaultElementText($item, $elementId);
            $hasDefaultValue = !empty($value);
        }

        // See if this element is configured to be a text field.
        $convertToTextBox = array_key_exists($elementId, $this->textFields);

        if ($hasDefaultValue || $convertToTextBox)
        {
            if ($convertToTextBox)
            {
                // This element should get rendered as a text box instead of as a multi-line TextArea which is the Omeka
                // default. A width of zero means max width. Note that this code cannot override the width of <select>
                // lists created by the SimpleVocab plugin because it gets called after this plugin (they are called
                // in alphabetical order). To set those widths, use CSS in AvantCustom/views/shared/css/avantcustom.css.
                $width = $this->textFields[$elementId]['width'];
                $components = self::createTextBox($components, $args, $width, $value);
            }
            else
            {
                if ($hasDefaultValue)
                {
                    // The field should remain a Text Area, but with a default value.
                    $components = self::createTextArea($components, $args, $value);
                }
            }
        }
        return $components;
    }

    private function createTextArea(array $components, $args, $value)
    {
        $inputNameStem = $args['input_name_stem'] . "[text]";
        $components['input'] = get_view()->formTextarea($inputNameStem, $value, array('rows'=>3));
        return $components;
    }

    private function createTextBox(array $components, $args, $width, $value)
    {
        $inputNameStem = $args['input_name_stem'] . "[text]";
        $width = $width == 0 ? 380 : $width;
        $components['input'] = get_view()->formText($inputNameStem, $value, array('style' => "width:{$width}px"));
        return $components;
    }

    public function filterDisplayElements($elementsBySet)
    {
        // This filter lets a plugin dynamically hide elements from the public user interface
        // in addition to those elements that are hidden by the HideElements plugin.

        if (is_admin_theme())
            return $elementsBySet;

        $dateValidator = new DateValidator();
        $elementsBySet = $dateValidator->hideStartEndYears($elementsBySet);

        return $elementsBySet;
    }

    public function filterElementForm($components, $args)
    {
        // Omeka calls the Element Form Filter to give plugins an opportunity to modify the Edit form's input-block
        // <div> for an element's <input> tag plus additional controls like the Add Input button.

        $elementName = $args['element']['name'];
        $elementId = $args['element']['id'];

        if ($this->elementCloning->cloning())
        {
            $elementSetName = $args['element']['set_name'];
            $components = $this->elementCloning->cloneElementValue($elementId, $elementSetName, $elementName, $components);
        }

        if ($elementName == 'Creator' || $elementName == 'Publisher')
        {
            // Check if this method is getting called via AJAX to add another input element.
            // Doing so clobbers the existing Suggest button with a new one, but the new one
            // no longer has a click handler. For now, just don't show the Suggest button anymore.
            $singleInput = !isset($args['options']['extraFieldCount']);
            if ($singleInput)
            {
                $suggestButton = '<button class="suggest-button" type="button">' . __('Suggest') . '</button>';
                $components['inputs'] .= $suggestButton;
            }
        }

        $components = $this->removeAddInputButton($elementId, $components);

        return $components;
    }

    public function filterElementInput(ElementValidator $elementValidator, $components, $args)
    {
        // Omeka calls the Element Input Filter to give this plugin an opportunity to modify an element's <input> tag.

        $item = $args['record'];
        $elementId = $args['element']['id'];

        // Create the appropriate field HTML (a text box or text area) for the element.
        $components = $this->createField($elementValidator, $components, $args, $item, $elementId);

        $allowHtml = array_key_exists($elementId, $this->htmlElements);
        if (!$allowHtml)
        {
            // Remove the HTML checkbox for this element.
            $components['html_checkbox'] = false;
        }

        // Return the modified HTML.
        return $components;
    }

    public function filterElementSave(ElementValidator $elementValidator, $text, $args)
    {
        // Omeka calls the Element Save Filter to give this plugin the opportunity to modify
        // the text that will be saved for an element.
        // Omeka calls this filter before calling filterElementValidate.

        $elementId = $args['element']['id'];
        $filteredText = $elementValidator->filterElementText($elementId, $text);
        return $filteredText;
    }

    public function filterElementValidate(ElementValidator $elementValidator, $args)
    {
        // Omeka calls the Element Validation Filter to give this plugin the opportunity to accept
        // or reject an element's text. The validation logic called from here rejects a value by
        // adding an error to the element's item. If not errors are added, the value is okay.
        // The method always returns true to prevent Omeka from adding it's own default error.
        // Omeka calls this filter after calling filterElementSave.

        $item = $args['record'];
        $elementId = $args['element']['id'];
        $elementName = $args['element']['name'];
        $text = $args['text'];
        $elementValidator->validateElementText($item, $elementId, $elementName, $text);

        $elementValidator->performCallbackValidation($item, $elementId, $elementName, $text);

        return true;
    }

    private function removeAddInputButton($elementId, $components)
    {
        $allowAddInputButton = array_key_exists($elementId, $this->addInputElements);

        if (!$allowAddInputButton)
        {
            $components['add_input'] = false;
        }
        return $components;
    }
}