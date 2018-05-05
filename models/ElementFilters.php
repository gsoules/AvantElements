<?php
class ElementFilters
{
    protected $addInputElements;
    protected $elementCloning;
    protected $inputElements;
    protected $fields;
    protected $htmlElements;

    public function __construct()
    {
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

    public function filterElementForm(ElementValidator $elementValidator, $components, $args)
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
        foreach ($this->inputElements[$elementId] as $inputElement)
        {
            $values = $inputElement['values'];
            $inputNameStem = $inputElement['stem'];
            $formControls = $inputElement['form_controls'];

            foreach ($values as $key => $value)
            {
                $stem = str_replace('[0]', "[$key]", $inputNameStem);
                $field = $this->fields->createField($elementValidator, $item, $elementId, $cloning, $value, $stem, $formControls);
                $components['inputs'] .= $field;
            }
        }

//        if ($elementName == 'Creator' || $elementName == 'Publisher')
//        {
//            // Check if this method is getting called via AJAX to add another input element.
//            // Doing so clobbers the existing Suggest button with a new one, but the new one
//            // no longer has a click handler. For now, just don't show the Suggest button anymore.
//            $singleInput = !isset($args['options']['extraFieldCount']);
//            if ($singleInput)
//            {
//                $suggestButton = '<button class="suggest-button" type="button">' . __('Suggest') . '</button>';
//                $components['inputs'] .= $suggestButton;
//            }
//        }

        $components = $this->hideAddInputButton($elementId, $components);

        return $components;
    }

    public function filterElementInput($components, $args)
    {
        // Omeka calls the Element Input Filter to give this plugin an opportunity to modify an element's <input> tag.
        // Omeka calls this filter before calling filterElementValidate.

        $item = $args['record'];
        $elementId = $args['element']['id'];

        $hasErrors = AvantElements::itemHasErrors($item);

        // Get the element's value.
        $values = array();
        if ($this->elementCloning->cloning())
        {
            if (isset($this->inputElements[$elementId]))
            {
                // A duplicated item is posting back with an error. It already has fields which must not be cloned again.
                return $components;
            }

            if ($hasErrors)
            {
                $values = AvantCommon::getPostedValues($elementId);
            }
            else
            {
                $elementName = $args['element']['name'];
                $values = $this->elementCloning->getCloneElementValues($elementName);
                if (empty($values))
                {
                    // There's no value to clone. Create a blank value for the duplicated item.
                    $values[] = '';
                }
            }
        }
        else
        {
            $values[] = $args['value'];
        }

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

        // Remember information about the element that's only available when this filter is called. It will
        // get used later when filterElementForm is called for this same element. Note that this filter is
        // called once for each instance of an element's value. If an element has more than one value, e.g.
        // two titles, the Edit form will display an input field for each title along with a red Remove
        // button for each. The code below puts the data for this instance into $inputElement and appends
        // the instance to the inputElements array indexed by the element's Id.
        $inputElement = array();
        $inputElement['values'] = $values;
        $inputElement['stem'] = $args['input_name_stem'] . "[text]";
        $inputElement['form_controls'] = $components['form_controls'];
        $this->inputElements[$elementId][] = $inputElement;

        // Return the modified HTML.
        return $components;
    }

    public function filterElementSave(ElementValidator $elementValidator, $text, $args)
    {
        // Omeka calls the Element Save Filter to give this plugin the opportunity to modify
        // the text that will be saved for an element.
        // Omeka calls this filter before calling filterElementValidate.

        $item = $args['record'];
        $elementId = $args['element']['id'];
        $filteredText = $elementValidator->filterElementText($item, $elementId, $text);
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

    private function hideAddInputButton($elementId, $components)
    {
        $allowAddInputButton = array_key_exists($elementId, $this->addInputElements);

        if (!$allowAddInputButton)
        {
            $components['add_input'] = '';
        }
        return $components;
    }
}