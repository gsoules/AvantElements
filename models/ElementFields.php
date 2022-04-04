<?php

class ElementFields
{
    protected $checkboxFields;
    protected $defaultValues;
    protected $readonlyFields;
    protected $selectFields;
    protected $textFields;
	protected $placeholders;
	protected $textareaRows;

    public function __construct()
    {
        $this->checkboxFields = ElementsConfig::getOptionDataForCheckboxField();
        $this->defaultValues = ElementsConfig::getOptionDataForDefaultValue();
        $this->readonlyFields = ElementsConfig::getOptionDataForReadOnlyField();
        $this->selectFields = ElementsConfig::getOptionDataForSelectField();
        $this->textFields = ElementsConfig::getOptionDataForTextField();
        $this->placeholders = ElementsConfig::getOptionDataForPlaceholder();
        $this->textareaRows = ElementsConfig::getOptionTextForTextareaRows();
    }

    public function createField(CustomCallback $customCallback, $item, $elementId, $cloning, $value, $inputName, $formControls, $htmlCheckbox)
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
                $value = $customCallback->performCallbackForElement(CustomCallback::CALLBACK_ACTION_DEFAULT, $item, $elementId);
            }
        }

        // See if this element is configured to be a text field.
        $convertToTextBox = array_key_exists($elementId, $this->textFields);
        $convertToCheckBox = array_key_exists($elementId, $this->checkboxFields);
        $fieldIsReadonly = array_key_exists($elementId, $this->readonlyFields) || array_key_exists(0, $this->readonlyFields);
        $vocabulary = AvantElements::getVocabularyTerms($elementId);
        $isSelect = !empty($vocabulary);
        $inputs = '';

        if ($convertToTextBox)
        {
            // Replace the Text Area with a Text Box.
            $inputs = self::createTextBox($value, $inputName, $this->getFieldWidth($this->textFields, $elementId), $this->getFieldPlaceholder($this->placeholders, $elementId));
        }
        else if ($convertToCheckBox)
        {
            // Replace the TextArea with a checkbox.
            $inputs = self::createCheckbox($value, $inputName);
        }
        elseif ($isSelect)
        {
            // Create the <select> needed to display SimpleVocab or AvantVocabulary lists.
            $inputs = self::createSelect($value, $inputName, $vocabulary, $elementId);
        }

        if (empty($inputs))
        {
            // The element is not a Text Box, Checkbox, or Select list. Emit a Text Area.
            $inputs = self::createTextArea($value, $inputName, $this->textareaRows, $this->getFieldPlaceholder($this->placeholders, $elementId));
        }

        // Don't let the user change an existing item's identifier because doing so would leave vestiges of
        // the old identifier in the Elasticsearch indexes and in AWS S3. Instead of adding logic to do the
        // necessary cleanup, simply prevent the situation from occurring.
        $identifierIsReadonly = !$isNewItem && $elementId == ItemMetadata::getIdentifierElementId() && !empty(ItemMetadata::getItemIdentifier($item));

        if ($fieldIsReadonly || $identifierIsReadonly)
        {
            // Insert a 'disabled' attribute at the end of the <input> tag.
            $inputs = str_replace('>', ' disabled>', $inputs);
        }

        // Wrap the input in the divs that Omeka expects for the form.
        $inputs = "<div class='input-block'><div class='input'>$inputs</div>$formControls $htmlCheckbox</div>";
        return $inputs;
    }

    protected function createCheckbox($value, $inputName)
    {
        return get_view()->formCheckbox($inputName, $value != null, array(), array('1', null));
    }

    protected function createSelect($value, $inputName, $vocabulary, $elementId)
    {
        $width = 0;
        if (array_key_exists($elementId, $this->selectFields))
        {
            // This element is configured in the AvantElements Vocabulary Field option list.
            // Use the configured size or if no size, the default of 0 which means max width.
            $width = $this->getFieldWidth($this->selectFields, $elementId);
            if ($width)
            {
                // Add a little extra to account for the dropdown arrow graphic.
                $width += 16;
            }
        }

        $style = $width == 0 ? '' : "width:{$width}px";

        // When the list is long, use the enhanced selected that provides a text area to type into to search the
        // list. On short lists, the text area isn't useful and could be confusing, so use the standard select.
        $class = count($vocabulary) > 8 ? 'avantelements-select' : '';

        $class .= $width == 0 ? ' input-field-full-width' : '';
        $selectTerms = array('' => __('Select Below')) + array_combine($vocabulary, $vocabulary);
        return get_view()->formSelect($inputName, $value, array('class' => $class, 'style' => $style), $selectTerms);
    }

    protected function createTextArea($value, $inputName, $textareaRows, $placeholder)
    {
        return get_view()->formTextarea($inputName, $value, array('rows' => $textareaRows, 'cols' => 50, 'placeholder' => $placeholder));
    }

    protected function createTextBox($value, $inputName, $width, $placeholder)
    {
        $style = $width == 0 ? '' : "width:{$width}px";
        $class = $width == 0 ? 'input-field-full-width' : '';
        return get_view()->formText($inputName, $value, array('class' => $class, 'style' => $style, 'placeholder' => $placeholder));
    }

    protected function getFieldWidth($fields, $elementId)
    {
        return isset($fields[$elementId]) ? $fields[$elementId]['width'] : 0;
    }
	
    protected function getFieldPlaceholder($fields, $elementId)
    {
    return isset($fields[$elementId]) ? $fields[$elementId]['placeholder'] : '';
    }
}
