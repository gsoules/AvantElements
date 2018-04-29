<?php

class ElementFields
{
    protected $textFields;

    public function __construct()
    {
        $this->textFields = ElementsConfig::getOptionDataForTextField();
    }

    public function createField(ElementValidator $elementValidator, $components, $args, $item, $elementId)
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

    protected function createTextArea(array $components, $args, $value)
    {
        $inputNameStem = $args['input_name_stem'] . "[text]";
        $components['input'] = get_view()->formTextarea($inputNameStem, $value, array('rows'=>3));
        return $components;
    }

    protected function createTextBox(array $components, $args, $width, $value)
    {
        $inputNameStem = $args['input_name_stem'] . "[text]";
        $width = $width == 0 ? 380 : $width;
        $components['input'] = get_view()->formText($inputNameStem, $value, array('style' => "width:{$width}px"));
        return $components;
    }

}