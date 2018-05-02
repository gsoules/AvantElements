<?php

class ElementFields
{
    protected $checkboxFields;
    protected $readonlyFields;
    protected $textFields;

    public function __construct()
    {
        $this->checkboxFields = ElementsConfig::getOptionDataForCheckboxField();
        $this->readonlyFields = ElementsConfig::getOptionDataForReadOnlyField();
        $this->textFields = ElementsConfig::getOptionDataForTextField();
    }

    public function createField(ElementValidator $elementValidator, $components, $item, $elementId, $value, $stem, $cloning)
    {
        // This method overrides Omeka's logic for emitting fields. Here's why:
        //    * Omeka only emits Text Area inputs, but AvantElements also supports Text Box inputs.
        //    * Omeka doesn't offer a way to provide default values or to clone values from other items.
        //    * The SimpleVocab plugin does not support default values and it uses inline styling for width.
        //
        // By emitting its own inputs, this method provides full control over form fields.

        if (empty($item->id) && !$cloning)
        {
            // This is a new item. See if there is a default value for this element.
            $value = $elementValidator->getCallbackDefaultElementText($item, $elementId);
        }
        $hasValue = !empty($value);

        // See if this element is configured to be a text field.
        $convertToTextBox = array_key_exists($elementId, $this->textFields);
        $convertToCheckBox = array_key_exists($elementId, $this->checkboxFields);
        $fieldIsReadonly = array_key_exists($elementId, $this->readonlyFields);

        if ($convertToTextBox)
        {
            // Emit a Text Box for this element whether or not it has a value.
            $width = $this->textFields[$elementId]['width'];
            $inputs = self::createTextBox($value, $stem, $width);
        }
        else if ($convertToCheckBox)
        {
            $inputs = self::createCheckbox($value, $stem);
        }
        elseif ($hasValue)
        {
            $vocabulary = $this->getSimpleVocabTerms($elementId);
            if (!empty($vocabulary))
            {
                // This element is configured with the SimpleVocab plugin.
                // Emit a Select List with a selected value to replace the one emitted by SimpleVocab.
                $inputs = self::createSelect($value, $stem, $vocabulary);
            }
            else
            {
                // Emit a Text Area with a value.
                $inputs = self::createTextArea($value, $stem);
            }
        }
        else
        {
            // The element is not a Text Box and has no value. Keep the Text Area emitted by Omeka.
            $inputs = $components['inputs'];
        }

        if ($fieldIsReadonly)
        {
            // Insert a 'disabled' attribute at the end of the <input> tag.
            $inputs = str_replace('>', ' disabled>', $inputs);
        }

        // Wrap the input in the divs that Omeka expects for the form.
        return "<div class='input-block'><div class='input'>$inputs</div></div>";
    }

    protected function createCheckbox($value, $stem)
    {
        return get_view()->formCheckbox($stem, (bool)$value, array(), array('1', '0'));
    }

    protected function createSelect($value, $stem, $vocabulary)
    {
        $style =  array('style' => 'width: 300px;');
        $selectTerms = array('' => __('Select Below')) + array_combine($vocabulary, $vocabulary);
        return get_view()->formSelect($stem, $value, $style, $selectTerms);
    }

    protected function createTextArea($value, $stem)
    {
        return get_view()->formTextarea($stem, $value, array('rows'=>3, 'readonly'=>true));
    }

    protected function createTextBox($value, $stem, $width)
    {
        $width = $width == 0 ? 380 : $width;
        return get_view()->formText($stem, $value, array('style' => "width:{$width}px"));
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