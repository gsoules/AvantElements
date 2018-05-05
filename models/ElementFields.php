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

    public function createField(ElementValidator $elementValidator, $item, $elementId, $cloning, $value, $stem, $formControls)
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

        // See if this element is configured to be a text field.
        $convertToTextBox = array_key_exists($elementId, $this->textFields);
        $convertToCheckBox = array_key_exists($elementId, $this->checkboxFields);
        $fieldIsReadonly = array_key_exists($elementId, $this->readonlyFields);
        $vocabulary = $this->getSimpleVocabTerms($elementId);
        $isSelect = !empty($vocabulary);
        $inputs = '';

        if ($convertToTextBox)
        {
            // Replace the Text Area with a Text Box.
            $inputs = self::createTextBox($value, $stem, $this->getFieldWidth($this->textFields, $elementId));
        }
        else if ($convertToCheckBox)
        {
            // Replace the TextArea with a checkbox.
            $inputs = self::createCheckbox($value, $stem);
        }
        elseif ($isSelect)
        {
            // Replace the <select> emitted by SimpleVocab.
            $inputs = self::createSelect($value, $stem, $vocabulary, $this->getFieldWidth($this->selectFields, $elementId));
        }

        if (empty($inputs))
        {
            // The element is not a Text Box, Checkbox, or Select list. Emit a Text Area.
            $inputs = self::createTextArea($value, $stem);
        }

        if ($fieldIsReadonly)
        {
            // Insert a 'disabled' attribute at the end of the <input> tag.
            $inputs = str_replace('>', ' disabled>', $inputs);
        }

        // Wrap the input in the divs that Omeka expects for the form.
        $inputs = "<div class='input-block'><div class='input'>$inputs</div>$formControls</div>";
        return $inputs;
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
        return get_view()->formTextarea($stem, $value, array('rows' => 3, 'cols' => 50));
    }

    protected function createTextBox($value, $stem, $width)
    {
        $style = $width == 0 ? '' : "width:{$width}px";
        $class = $width == 0 ? 'input-field-full-width' : '';
        return get_view()->formText($stem, $value, array('class' => $class, 'style' => $style));
    }

    protected function getFieldWidth($fields, $elementId)
    {
        return isset($fields[$elementId]) ? $fields[$elementId]['width'] : 0;
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