<?php

class ElementFields
{
    protected $textFields;

    public function __construct()
    {
        $this->textFields = ElementsConfig::getOptionDataForTextField();
    }

    public function createField(ElementValidator $elementValidator, $components, $args, $item, $elementId, $value, $stem)
    {
        // This method determines if the element's Text Area needs to be modified or converted to a Text Box.
        // A Text Area is modified when it needs to display a default value for a new item. A TextArea is
        // converted to a Text Box when the element is specified as a Text Field on the configuration page.
        // This logic takes a brute force approach to performing these tasks. It's easier and safer to simply
        // replace the existing <input> HTML for the field than to parse and modify the existing HTML. The
        // HTML for a field is contained in the $components array's 'input' element.

        $hasDefaultValue = false;

        if (empty($item->id))
        {
            // This is a new item. See if there is a default value for this element.
            $value = $elementValidator->getCallbackDefaultElementText($item, $elementId);
            $hasDefaultValue = !empty($value);
        }

        // See if this element is configured to be a text field.
        $convertToTextBox = array_key_exists($elementId, $this->textFields);

        $inputs = $components['inputs'];
        if ($hasDefaultValue || $convertToTextBox)
        {
            if ($convertToTextBox)
            {
                // This element should get rendered as a text box instead of as a multi-line TextArea which is the Omeka
                // default. A width of zero means max width. Note that this code cannot override the width of <select>
                // lists created by the SimpleVocab plugin because it gets called after this plugin (they are called
                // in alphabetical order). To set those widths, use CSS in AvantCustom/views/shared/css/avantcustom.css.
                $width = $this->textFields[$elementId]['width'];
                $inputs = self::createTextBox($args, $width, $value, $stem);
            }
            else
            {
                if ($hasDefaultValue)
                {
                    // The field should remain a Text Area, but with a default value.
                    $vocabulary = $this->getSimpleVocabTerms($elementId);
                    if (empty($vocabulary))
                    {
                        $inputs = self::createTextArea($args, $value, $stem);
                    }
                    else
                    {
                        $inputs = self::createSelect($args, $value, $vocabulary, $stem);
                    }
                }
            }
        }

        return "<div class='input-block'><div class='input'>$inputs</div></div>";
    }

    protected function createSelect($args, $value, $vocabulary, $stem)
    {
        $style =  array('style' => 'width: 300px;');
        $selectTerms = array('' => __('Select Below')) + array_combine($vocabulary, $vocabulary);
        $inputs = get_view()->formSelect($stem, $value, $style, $selectTerms);
        return $inputs;
    }

    protected function createTextArea($args, $value, $stem)
    {
        $inputs = get_view()->formTextarea($stem, $value, array('rows'=>3));
        return $inputs;
    }

    protected function createTextBox($args, $width, $value, $stem)
    {
        $width = $width == 0 ? 380 : $width;
        $inputs = get_view()->formText($stem, $value, array('style' => "width:{$width}px"));
        return $inputs;
    }

    public static function getSimpleVocabTerms($elementId)
    {
        $vocabulary = array();
        if (plugin_is_active('SimpleVocab'))
        {
            $simpleVocabTerm = get_db()->getTable('SimpleVocabTerm')->findByElementId($elementId);
            if (!empty($simpleVocabTerm))
            {
                $vocabulary = explode("\n", $simpleVocabTerm->terms);
            }
        }
        return $vocabulary;
    }
}