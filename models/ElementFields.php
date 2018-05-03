<?php

class ElementFields
{
    protected $checkboxFields;
    protected $defaultValues;
    protected $readonlyFields;
    protected $selectFields;
    protected $textFields;

    public function __construct()
    {
        $this->checkboxFields = ElementsConfig::getOptionDataForCheckboxField();
        $this->defaultValues = ElementsConfig::getOptionDataForDefaultValue();
        $this->readonlyFields = ElementsConfig::getOptionDataForReadOnlyField();
        $this->selectFields = ElementsConfig::getOptionDataForSelectField();
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

        $isNewItem = empty($item->id) && !$cloning;
        if ($isNewItem)
        {
            // This is a new item. See if there is a default value for this element.
            if (array_key_exists($elementId, $this->defaultValues))
            {
                $value = $this->defaultValues[$elementId]['value'];
            }
            if (strlen($value) == 0)
            {
                // There is no configured default value. Check for a custom callback value.
                // Note that a configured value takes precedence over a custom value.
                $value = $elementValidator->getCallbackDefaultElementText($item, $elementId);
            }
        }
        $hasValue = strlen($value) > 0;

        // See if this element is configured to be a text field.
        $convertToTextBox = array_key_exists($elementId, $this->textFields);
        $convertToCheckBox = array_key_exists($elementId, $this->checkboxFields);
        $fieldIsReadonly = array_key_exists($elementId, $this->readonlyFields);
        $vocabulary = $this->getSimpleVocabTerms($elementId);
        $isSelect = !empty($vocabulary);

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
        elseif ($hasValue || $isSelect)
        {
            if ($isSelect)
            {
                // This element is configured with the SimpleVocab plugin.
                // Emit a Select List with a selected value to replace the one emitted by SimpleVocab.
                $hasWidth = isset($this->selectFields[$elementId]);
                $width = $hasWidth ? $this->selectFields[$elementId]['width'] : 0;
                $inputs = self::createSelect($value, $stem, $vocabulary, $width);
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

    protected function createSelect($value, $stem, $vocabulary, $width)
    {
        $style = $width == 0 ? '' : "width:{$width}px";
        $class = $width == 0 ? 'input-field-full-width' : '';
        $selectTerms = array('' => __('Select Below')) + array_combine($vocabulary, $vocabulary);
        return get_view()->formSelect($stem, $value, array('class' => $class, 'style' => $style), $selectTerms);
    }

    protected function createTextArea($value, $stem)
    {
        return get_view()->formTextarea($stem, $value, array('rows'=>3, 'readonly'=>true));
    }

    protected function createTextBox($value, $stem, $width)
    {
        $style = $width == 0 ? '' : "width:{$width}px";
        $class = $width == 0 ? 'input-field-full-width' : '';
        return get_view()->formText($stem, $value, array('class' => $class, 'style' => $style));
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