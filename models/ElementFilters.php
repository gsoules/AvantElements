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
        $elementName = $args['element']['name'];
        $elementId = $args['element']['id'];

        // Create the appropriate field HTML (a Text Box, Text Area, or Select List) for the element.
        $value = $this->inputElements[$elementId]['value'];
        $stem = $this->inputElements[$elementId]['stem'];
        $cloning = $this->elementCloning->cloning();
        $components['inputs'] = $this->fields->createField($elementValidator, $components, $item, $elementId, $value, $stem, $cloning);

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

    public function filterElementInput($components, $args)
    {
        // Omeka calls the Element Input Filter to give this plugin an opportunity to modify an element's <input> tag.
        // Omeka calls this filter before calling filterElementValidate.

        $elementId = $args['element']['id'];

        // Get the element's value.
        if ($this->elementCloning->cloning())
        {
            $elementName = $args['element']['name'];
            $elementSetName = $args['element']['set_name'];
            $value = $this->elementCloning->getCloneElementValue($elementSetName, $elementName);
        }
        else
        {
            $value = $args['value'];
        }

        // Remember the element's value and stem so that this information will be available when
        // filterElementForm is called for this same element.
        $this->inputElements[$elementId]['value'] = $value;
        $this->inputElements[$elementId]['stem'] = $args['input_name_stem'] . "[text]";

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