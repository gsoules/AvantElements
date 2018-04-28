<?php
class ElementFilters
{
    protected $addInputElements;
    protected $cloneItem;
    protected $cloneItemId;
    protected $cloning;
    protected $htmlElements;
    protected $textFields;

    public function __construct()
    {
        $this->addInputElements = ElementsConfig::getOptionDataForAddInput();
        $this->textFields = ElementsConfig::getOptionDataForTextField();
        $this->htmlElements = ElementsConfig::getOptionDataForHtml();
    }

    protected function cloneElementValue($elementId, $elementSetName, $elementName, $components)
    {
        if (!isset($this->request))
        {
            $this->request = Zend_Controller_Front::getInstance()->getRequest();
            $this->cloneItemId = $this->request->getParam('id');
            $this->cloneItem = ItemMetadata::getItemFromId($this->cloneItemId);
            $this->cloning = !empty($this->cloneItemId) && $this->request->getParam('action') == 'add';
        }

        if (!$this->cloning)
            return $components;

        if ($elementName == 'Access DB')
            return $components;

        $value = metadata($this->cloneItem, array($elementSetName, $elementName), array('no_filter' => true));

        if ($elementName == ItemMetadata::getTitleElementName())
            $value = "CLONED: $value";

        if (!empty($value))
        {
            // Replace the elements input HTML with a simple text element that contains the cloned value.
            // This is much simpler than parsing TextAreas and Select lists to insert the value.
            $components['inputs'] = "<div class='input-block'><div class='input'><input type='text' name='Elements[$elementId][0][text]' id='Elements-$elementId-0-text' value='$value' style='width:400px;'></div></div>";
        }

        return $components;
    }

    private function convertTextAreaToText(array $components, $args, $width)
    {
        // Change this element's textarea box to a plain text box, but first, append "[text]" to the end of
        // the input name stem. This causes "[text]" to be added to the end of the input tag's name attribute
        // and "-text" to be added to the end of the id attribute. By doing this, the modified input tag's name
        // and id match the original textarea tag's name and id. If we don't do this an internal error occurs
        // when saving the form, presumably because of name/id mismatch. This issue does not appear to documented,
        // but the solution makes sense and seems to work.
        $input_name_stem = $args['input_name_stem'] . "[text]";
        $width = $width == 0 ? 380 : $width;
        $components['input'] = get_view()->formText($input_name_stem, $args['value'], array('style' => "width:{$width}px"));

        return $components;
    }

    public function filterDisplayElements(DateValidator $dateValidator, $elementsBySet)
    {
        // This filter lets a plugin dynamically hide elements from the public user interface
        // in addition to those elements that are hidden by the HideElements plugin.

        if (is_admin_theme())
            return $elementsBySet;

        $elementsBySet = $dateValidator->hideStartEndYears($elementsBySet);

        return $elementsBySet;
    }

    public function filterElementForm($components, $args)
    {
        // Omeka calls the Element Form Filter to give plugins an opportunity to modify the Edit form's input-block
        // <div> for an element's <input> tag plus additional controls like the Add Input button.

        $elementName = $args['element']['name'];
        $elementId = $args['element']['id'];

        if ($elementName == "Identifier")
        {
            $components = $this->replaceEmptyIdentifierWithDefaultValue($components);
        }
        else
        {
            $elementSetName = $args['element']['set_name'];
            $components = $this->cloneElementValue($elementId, $elementSetName, $elementName, $components);
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

    public function filterElementInput($components, $args)
    {
        // Omeka calls the Element Input Filter to give this plugin an opportunity to modify an element's <input> tag.

        $elementId = $args['element']['id'];

        if (array_key_exists($elementId, $this->textFields))
        {
            // This element should get rendered as a text box instead of as a multi-line TextArea which is the Omeka
            // default. A width of zero means max width. Note that this code cannot override the width of <select>
            // lists created by the SimpleVocab plugin because it gets called after this plugin (they are called
            // in alphabetical order). To set those widths, use CSS in AvantCustom/views/shared/css/avantcustom.css.
            $width = $this->textFields[$elementId]['width'];
            $components = self::convertTextAreaToText($components, $args, $width);
        }

        $allowHtml = array_key_exists($elementId, $this->htmlElements);
        if (!$allowHtml)
        {
            // Remove the HTML checkbox for this element.
            $components['html_checkbox'] = false;
        }

        // Return the modified HTML.
        return $components;
    }

    public function filterElementSave(ItemValidator $itemValidator, $text, $args)
    {
        // Omeka calls the Element Save Filter to give this plugin the opportunity to modify
        // the text that will be saved for an element.
        // Omeka calls this filter before calling filterElementValidate.

        $elementId = $args['element']['id'];
        $filteredText = $itemValidator->filterElementText($elementId, $text);
        return $filteredText;
    }

    public function filterElementValidate(ItemValidator $itemValidator, $isValid, $args)
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
        $itemValidator->validateElementText($item, $elementId, $elementName, $text);

        $itemValidator->performCallbackValidation($item, $elementId, $elementName, $text);

        return true;
    }

    private function getNextIdentifier()
    {
//        $elementTable = get_db()->getTable('Element');
//        $identifierParts = ItemMetadata::getPartsForIdentifierElement();
//        $element = $elementTable->findByElementSetNameAndElementName($identifierParts[0], $identifierParts[1]);
//        $elementId = $element->id;
        $elementId = ItemMetadata::getIdentifierElementId();
        $db = get_db();
        $sql = "SELECT MAX(CAST(text AS SIGNED)) AS next_element_id FROM `{$db->ElementTexts}` where element_id = $elementId";
        $result = $db->query($sql)->fetch();
        $nextElementId = $result['next_element_id'] + 1;
        return $nextElementId;
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

    protected function replaceEmptyIdentifierWithDefaultValue($components)
    {
        $inputs = $components['inputs'];
        $isBlank = strpos($inputs, 'value=""') !== false;
        if ($isBlank) {
            // The Identifier has no value. Assume that a new record is being added and provide a default value.
            // It seems like there should be a cleaner way to do this than to edit the <input> tag HTML, but
            // until we find out how, this does the job.
            $identifier = $this->getNextIdentifier();
            $components['inputs'] = str_replace('value=""', 'value="' . $identifier . '"', $inputs);
        }
        return $components;
    }

}