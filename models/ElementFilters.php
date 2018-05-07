<?php
class ElementFilters
{
    protected $addInputElements;
    protected $customCallback;
    protected $elementCloning;
    protected $elementValidator;
    protected $inputElements;
    protected $fields;
    protected $htmlElements;

    public function __construct(CustomCallback $customCallback, ElementValidator $elementValidator)
    {
        $this->customCallback = $customCallback;
        $this->elementValidator = $elementValidator;
        $this->addInputElements = ElementsConfig::getOptionDataForAddInput();
        $this->elementCloning = new ElementCloning();
        $this->inputElements = array();
        $this->fields = new ElementFields();
        $this->htmlElements = ElementsConfig::getOptionDataForHtml();
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
        // Omeka calls this filter after calling filterElementInput. Note that SimpleVocab emits a select list
        // when its ElementInput filter is called. Because this filter gets called after that filter, this method
        // can overwrite SimpleVocab's list with its own.

        $item = $args['record'];
        $elementId = $args['element']['id'];
        $cloning = $this->elementCloning->cloning();

        // Create the input field HTML for each instance of the element's value.
        // See the comments in filterElementInput to learn how the inputElements array gets created.
        $components['inputs'] = '';
        foreach ($this->inputElements[$elementId] as $index => $inputElement)
        {
            $formControls = $inputElement['form_controls'];
            $value = $inputElement['value'];
            $inputName = "Elements[$elementId][$index][text]";

            $field = $this->fields->createField($this->customCallback, $item, $elementId, $cloning, $value, $inputName, $formControls);

            $components['inputs'] .= $field;
        }

        $components = $this->hideAddInputButton($elementId, $components);

        if (get_option(ElementsConfig::OPTION_HIDE_DESCRIPTIONS))
        {
            $components['description'] = '';
        }

        return $components;
    }

    public function filterElementInput($components, $args)
    {
        // Omeka calls the Element Input Filter to give this plugin an opportunity to modify an element's <input> tag.
        // Omeka calls this filter before calling filterElementValidate.
        // This method does not modify the <input> tag, but records information about it for use by filterElementForm.

        $item = $args['record'];
        $elementId = $args['element']['id'];
        $values = array();

        // Determine the circumstances under which this method is being called and handle accordingly.
        if (AvantElements::itemHasErrors($item))
        {
            // The Edit form has posted back with errors. It doesn't matter whether the item is a clone.
            // Save the element's single posted value in the $values array.
            $values = $this->getPostedValues($elementId, $values, $args['index']);
        }
        elseif ($this->elementCloning->cloning())
        {
            // The Edit form is being displayed for the first time to clone another item.
            // Save all of the cloned element's values in the $values array.
            $values = $this->elementCloning->getCloneElementValues($args['element']['name']);
        }
        else
        {
            // The Edit form is being displayed for the first time to edit an item, or it posted back
            // when the user clicked the Add Input button. Save the element's single value in the $values array.
            $values[] = $args['value'];
        }

        // Hide the HTML checkbox and/or the Remove button if the element doesn't need them.
        $components = $this->hideUnusedFormControls($components, $elementId);

        // Record information about the element that's only available when this filter is called. It will
        // get used later when filterElementForm is called for this same element. Note that this filter is
        // normally called once for each instance of an element's value. If an element has more than one
        // value, e.g. two titles, the Edit form will display an input field for each title along with a red
        // Remove button for each. This filter will get called once for each title. The code below records the
        // value for each instance into $inputElement and appends the instance to the $inputElements array
        // indexed by the element's Id. However, when cloning, an element's multiple values are captured above
        // in the $values array. The loop below ensures that there will still be in inputElement instance for
        // each value of the element being cloned. When filterElementForm is called, the $inputElements array
        // will contain value instances for both normal and cloning cases.
        foreach ($values as $value)
        {
            $inputElement = array();
            $inputElement['value'] = $value;
            $inputElement['form_controls'] = $components['form_controls'];
            $this->inputElements[$elementId][] = $inputElement;
        }

        // Return the modified HTML.
        return $components;
    }

    public function filterElementSave($text, $args)
    {
        // Omeka calls the Element Save Filter to give this plugin the opportunity to modify
        // the text that will be saved for an element.
        // Omeka calls this filter before calling filterElementValidate.

        $item = $args['record'];
        $elementId = $args['element']['id'];
        $filteredText = $this->filterElementTextBeforeSave($item, $elementId, $text);
        return $filteredText;
    }

    public function filterElementTextBeforeSave($item, $elementId, $text)
    {
        if (strlen($text) == 0)
        {
            // The string has no content. Note use of strlen() instead of empty()
            // to safely detect that a boolean value "0" is not considered empty.
            return $this->customCallback->performCallbackForElement(CustomCallback::CALLBACK_ACTION_DEFAULT, $item, $elementId);
        }

        if ($this->elementValidator->hasValidationDefinitionFor($elementId, ElementValidator::VALIDATION_TYPE_YEAR))
        {
            $text = trim($text);
        }

        if ($this->elementValidator->hasValidationDefinitionFor($elementId, ElementValidator::VALIDATION_TYPE_SIMPLE_TEXT))
        {
            $text = $this->filterSimpleText($text);
        }
        return $text;
    }

    public function filterElementValidate($args)
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

        $this->elementValidator->validateElementText($item, $elementId, $elementName, $text);
        $this->customCallback->performCallbackForElement(CustomCallback::CALLBACK_ACTION_VALIDATE, $item, $elementId, $text);

        return true;
    }

    protected function filterSimpleText($text)
    {
        // Remove carriage returns and tabs.
        $text = str_replace(array("\r", "\n", "\t"), '', $text);

        // Trim away leading or trailing whitespace, carriage returns, and tabs.
        $text = trim($text);

        // Replace en or em dashes with hyphens.
        $en_dash = html_entity_decode('&#x2013;', ENT_COMPAT, 'UTF-8');
        $em_dash = html_entity_decode('&#8212;', ENT_COMPAT, 'UTF-8');
        $text = str_replace(array($en_dash, $em_dash), '-', $text);

        return $text;
    }

    protected function getPostedValues($elementId, $values, $index)
    {
        $value = '';

        // See if the form contains an input for this element (it won't if the element's field is blank).
        if (isset($_POST['Elements'][$elementId]))
        {
            // The form contains an input for this element. Get all of the values for this element. Normally
            // these is just one value, but the user can add more with the Add Input button.
            $postedValues = $_POST['Elements'][$elementId];

            // Map the index to what's in the form by taking into account the fact that an element can have multiple
            // fields and that the user can use the Remove button to delete fields. Note that unlike the Add Input
            // button which posts back to the server to add a new field, the Remove button invokes jQuery which
            // simple deletes the <input> tag from the form.
            //
            // Normally, multiple field values are named in the form like this: Elements[50][0], Elements[50][1],
            // etc. where Elements is an array in the form, 50 is the element Id, and the last number is an index
            // indicating which field (first, second etc.) If the user clicks the Remove button to delete the first
            // field, the second field is still named Elements[50][1] which means its index is no longer accurate.
            // However, this method references fields using a zero-based index ($args['index']). The loop below maps
            // that index to what's in the form's Elements array to ensure that the correct fields value is obtained.
            $i = 0;
            foreach ($postedValues as $postedValue)
            {
                if ($index == $i)
                {
                    $value = $postedValue['text'];
                    break;
                }
                $i++;
            }
        }
        $values[] = $value;
        return $values;
    }

    private function hideAddInputButton($elementId, $components)
    {
        $allowAddInputButton = array_key_exists($elementId, $this->addInputElements);

        if (!$allowAddInputButton)
        {
            $components['add_input'] = '';

        }
        return $components;
    }

    protected function hideUnusedFormControls($components, $elementId)
    {
        $allowHtml = array_key_exists($elementId, $this->htmlElements);
        if (!$allowHtml)
        {
            // Remove the HTML for the HTML checkbox for this element.
            $components['html_checkbox'] = '';
        }

        $allowAddInputButton = array_key_exists($elementId, $this->addInputElements);
        if (!$allowAddInputButton)
        {
            // Remove the HTML for the red 'Remove' button that is emitted for every element. Note that that
            // Remove buttons are styled as display:none, but when an element has more then one input field,
            // jQuery displays the button for each field. Since this element can't have more than one input
            // field, the hidden Remove button isn't needed.
            $components['form_controls'] = '';
        }
        return $components;
    }
}