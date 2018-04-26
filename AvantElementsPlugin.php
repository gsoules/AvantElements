<?php

class AvantElementsPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $avantElements;
    protected $cloneItem;
    protected $cloneItemId;
    protected $cloning;
    protected $fieldWidths;
    protected $externalLinkDefinitions = array();
    protected $htmlElements;
    protected $linkBuilder;
    protected $multiInputElements;
    protected $request;
    protected $titleTextsBeforeSave;

    protected $_hooks = array(
        'admin_head',
        'admin_footer',
        'after_save_item',
        'before_save_item',
        'config',
        'config_form',
        'define_routes',
        'initialize',
        'install',
        'public_head'
    );

    protected $_filters = array(
        'display_elements'
    );

    public function __construct()
    {
        parent::__construct();

        $this->avantElements = new AvantElements();
        $this->linkBuilder = new LinkBuilder($this->_filters);
        $this->multiInputElements = ElementsOptions::getOptionDataForAddInput();
        $this->htmlElements = ElementsOptions::getOptionDataForHtml();
        $this->fieldWidths = ElementsOptions::getOptionDataForWidths();
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'filterLink') === 0)
        {
            $text = $this->linkBuilder->buildLink($name, $arguments);
            return $text;
        }

        return null;
    }

    private function addError($args, $message)
    {
        $elementName = $args['element']['name'];
        $item = $args['record'];
        $item->addError($elementName, $message);
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
        $components['input'] = get_view()->formText($input_name_stem, $args['value'], array('style' => 'width: ' . $width . 'px;'));

        return $components;
    }

    protected static function fetchElementsByValue($elementId, $value)
    {
        if (empty($value))
            return;
        $db = get_db();
        $select = $db->select()
            ->from($db->ElementText)
            ->where('element_id = ?', $elementId)
            ->where('text = ?', $value)
            ->where('record_type = ?', 'Item');
        $results = $db->getTable('ElementText')->fetchObjects($select);
        return $results;
    }

    public function filterDisplayElements($elementsBySet)
    {
        // This filter lets us dynamically hide elements from the public user interface
        // in addition to those elements that are hidden by the HideElements plugin.

        if (is_admin_theme())
            return $elementsBySet;

        $elementsBySet = $this->hideStartEndDates($elementsBySet);

        return $elementsBySet;
    }

    public function filterElementForm($components, $args)
    {
        // This filter lets us modify the input-block <div> that contains an element's
        // <input> tag plus additional controls like the Add Input button and Use HTML checkbox.

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
        // This filter lets us modify an element's <input> tag. It also lets us prevent
        // Omeka from emitting a Use HTML checkbox when the element is emitted on the input form.

        $elementId = $args['element']['id'];

        // Determine the width for the element's text field. Note that we cannot override the width of <select>
        // lists created by the SimpleVocab plug in. To set those field widths, use the CSS in
        // AvantCustom/views/shared/css/avantcustom.css.

        $width = 0;
        if (array_key_exists($elementId, $this->fieldWidths))
       {
           $width = $this->fieldWidths[$elementId]['value'];
       }

        // Change the TextArea to a Text box of the specified width. If no width is configured
        // for this element, it's field will remain a multi-line TextArea.
        if ($width > 0)
        {
            $components = self::convertTextAreaToText($components, $args, $width);
        }

        // Remove the HTML checkbox except for a few elements that use it.
        $allowHtml = array_key_exists($elementId, $this->htmlElements);

        if (!$allowHtml)
        {
            $components['html_checkbox'] = false;
        }

        return $components;
    }


    public function filterElementSave($text, $args)
    {
        return $text;
    }

    public function filterElementValidate($isValid, $args)
    {
        $elementId = $args['element']['id'];
        $elementName = $args['element']['name'];
        $text = $args['text'];
        $item = $args['record'];
        return $this->avantElements->validateElement($item, $elementId, $elementName, $text);
    }

    public function filterValidateDateEnd($isValid, $args)
    {
        return $this->filterValidateDateStart($isValid, $args);
    }

    public function filterValidateDateStart($isValid, $args)
    {
        $text = trim($args['text']);
        if (strlen($text) != 4 || !ctype_digit($text)) {
            $this->addError($args, 'Value must be a year consisting of exactly four digits');
        }

        return true;
    }

    public function filterValidateIdentifier($isValid, $args)
    {
        // Get the Identifier element and its text from the form being posted.
        $element = $args['element'];
        $text = trim($args['text']);

        // Make sure the value is an integer.
        if (!ctype_digit($text)) {
            $this->addError($args, 'Value must be a number consisting only of the digits 0 - 9');
            return true;
        }

        // Search the database to see if another Item has this identifier.
        $items = get_records('Item', array( 'advanced' => array( array('element_id' => $element->id, 'type' => 'is exactly', 'terms' => $text ))));

        if ($items){
            // Found an Item with this identifier. Check if it's the Item being saved or another Item.
            $savedItem = $args['record'];
            $foundItem = $items[0];
            if ($savedItem->id != $foundItem->id) {
                $nextElementId = $this->getNextIdentifier();
                $this->addError($args, "$text is used by another item. Next available Identifier is $nextElementId.");
            }
        }
        return true;
    }

    protected function getItemType($item)
    {
        return metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));
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

    protected function getTitleTexts($item)
    {
        $elementTable = get_db()->getTable('Element');
        $titleElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Title');
        return $item->getElementTextsByRecord($titleElement);
    }

    protected function hideStartEndDates($elementsBySet)
    {
        // Hide the Date Start and Date End elements when they both show the same value.
        $item = get_current_record('item');
        $dateStart = ItemMetadata::getElementTextFromElementName($item, array('Item Type Metadata', 'Date Start'));
        $dateEnd = ItemMetadata::getElementTextFromElementName($item, array('Item Type Metadata', 'Date End'));

        if ($dateStart == $dateEnd) {
            // Get the name of the item type metadata set to use as an index into the array of element sets.
            // Normally this isn't necessary, but it is when filtering elements.
            $itemTypeName = metadata($item, 'item type name');
            $itemTypeElementSetName = $itemTypeName . ' ' . ElementSet::ITEM_TYPE_NAME;

            // Remove the Date Start and Date End elements from the element set so they won't be displayed.
            unset($elementsBySet[$itemTypeElementSetName]['Date Start']);
            unset($elementsBySet[$itemTypeElementSetName]['Date End']);
        }

        return $elementsBySet;
    }

    public function hookAdminFooter($args)
    {
        echo get_view()->partial('/suggest-script.php');
    }

    public function hookAdminHead($args)
    {
        queue_css_file('avantelements-admin');
    }

    public function hookAfterSaveItem($args)
    {
        $item = $args['record'];
        $this->updateElementsRelatedToTitle($item);
    }

    public function hookBeforeSaveItem($args)
    {
        $item = $args['record'];
        $elementTable = get_db()->getTable('Element');

        $this->validateIdentifier($item);
        $this->validateDates($item, $elementTable);
        $this->validateLocation($item, $elementTable);
        $this->validateStatus($item, $elementTable);
        $this->validateTitle($item, $elementTable);
        $this->validateCreator($item, $elementTable);
        $this->validateRequiredFields($item, $elementTable);

        $this->titleTextsBeforeSave = $this->getTitleTexts($item);
    }

    public function hookConfig()
    {
        ElementsOptions::saveConfiguration();
    }

    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
    }

    public function hookInstall()
    {
        return;
    }

    public function hookInitialize()
    {
        // Add callbacks for every element even though some elements require no filtering or validation.
        $elements = get_db()->getTable('Element')->findAll();

        foreach ($elements as $element)
        {
            $set = $element->set_name;
            $name = $element->name;
            add_filter(array('ElementForm', 'Item', $set, $name), array($this, 'filterElementForm'));
            add_filter(array('ElementInput', 'Item', $set, $name), array($this, 'filterElementInput'));
            add_filter(array('Save', 'Item', $set, $name), array($this, 'filterElementSave'));
            add_filter(array('Validate', 'Item', $set, $name), array($this, 'filterElementValidate'));
        }
    }

    public function hookPublicHead($args)
    {
        queue_css_file('avantelements');
    }

    protected function itemTypeIsArticle($item)
    {
        $itemType = $this->getItemType($item);
        return strpos($itemType, "Article,") === 0;
    }

    protected function parseDate($date)
    {
        $date = strtok($date, " ");

        $matches = '';
        $year = "0000";
        $month = "01";
        $day = "01";

        $formatOk = true;

        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches))
        {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
        }
        elseif (preg_match("/^(\d{4})-(\d{2})$/", $date, $matches))
        {
            $year = $matches[1];
            $month = $matches[2];
        }
        elseif (preg_match("/^(\d{4})$/", $date, $matches))
        {
            $year = $matches[1];
        }
        else
        {
            $formatOk = false;
        }
        return array($year, $month, $day, $formatOk);
    }

    private function removeAddInputButton($elementId, $components)
    {
        $allowAddInputButton = array_key_exists($elementId, $this->multiInputElements);

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

    protected function updateElementsRelatedToTitle($item)
    {
        // Update any Creator or Publisher elements that have the value of this item's Title element.

        $titleTextsAfterSave = $this->getTitleTexts($item);

        if (count($titleTextsAfterSave) != 1)
        {
            // Do not perform the update for an item that has more than one title.
            return;
        }

        $oldTitleText = $this->titleTextsBeforeSave[0]['text'];
        $newTitleText = $titleTextsAfterSave[0]['text'];

        if ($oldTitleText == $newTitleText)
            return;

        $this->updateElementText('Creator', $oldTitleText, $newTitleText);
        $this->updateElementText('Publisher', $oldTitleText, $newTitleText);
    }

    protected function updateElementText($elementName, $oldTitleText, $newTitleText)
    {
        /* @var $element ElementText */
        $elementId = ItemMetadata::getElementIdForElementName($elementName);
        $elements = self::fetchElementsByValue($elementId, $oldTitleText);
        foreach ($elements as $element)
        {
            // Update the element.
            $element->text = $newTitleText;
            $element->save();

            // Update the text in the Search Texts table.
            $db = get_db();
            $select =  get_db()->select()->from($db->SearchTexts)->where('record_id = ?', $element->record_id);
            $searchText = $db->getTable('SearchText')->fetchObject($select);
            $text = $searchText['text'];
            $searchText['text'] = str_replace($oldTitleText, $newTitleText, $text);
            $searchText->save();
        }
    }

    public function validateAccessDB($item, $elementTable, $accessDBValue)
    {
        $identifierElementId = ItemMetadata::getIdentifierElementId();
        $identifierValue = $_POST['Elements'][$identifierElementId][0]['text'];
        $id = (int)$identifierValue;

        if ($id == 0)
            return;

        $isAccessItem = $id >= 5000 && $id <= 12754;
        $hasAccessDBValue = !empty($accessDBValue);

        if ($isAccessItem) {
            if (!$hasAccessDBValue) {
                $item->addError('Access DB', "This Access DB item must have an Access DB value of Converted or Unconverted.");
                return;
            }
        } else {
            if ($hasAccessDBValue) {
                $item->addError('Access DB', "This item did not come from Access. Choose 'Select Below' for the Access DB field.");
                return;
            }
        }
    }

    protected function validateCreator($item, $elementTable)
    {
        $creatorElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Creator');
        $creatorValue = $_POST['Elements'][$creatorElement->id][0]['text'];
        $this->validateRestrictedElementValue($item, 'Creator', $creatorValue);
    }

    protected function validateDates($item, $elementTable)
    {
        // Make sure Date Start and Date End have values if Date has a value.
        $dateElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Date');
        $dateStartElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Date Start');
        $dateEndElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Date End');

        $dateText = $_POST['Elements'][$dateElement->id][0]['text'];
        $dateStartText = $_POST['Elements'][$dateStartElement->id][0]['text'];
        $dateEndText = $_POST['Elements'][$dateEndElement->id][0]['text'];

        // Date, Date Start, and Date End are all empty.
        if (empty($dateText) && empty($dateStartText) && empty($dateEndText))
        {
            return;
        }

        list($dateYear, $month, $day, $formatOk) = $this->parseDate($dateText);
        if (!empty($dateText) && !$formatOk)
            return;

        list($dateStartYear, $month, $day, $formatOk) = $this->parseDate($dateStartText);
        if (!empty($dateStartText) && !$formatOk)
            return;

        list($dateEndYear, $month, $day, $formatOk) = $this->parseDate($dateEndText);
        if (!empty($dateEndText) && !$formatOk)
            return;

        if (empty($dateText))
        {
            if ($dateStartYear == $dateEndYear)
            {
                $item->addError('Dates', "When Date is empty, Date Start and Date End must each be set to a different year");
                return;
            }
        }
        else
        {
            if ($dateStartYear != $dateYear || $dateEndYear != $dateYear)
            {
                $item->addError('Dates', "When Date is set, Date Start and Date End must be set to the same year as Date");
                return;
            }
        }
    }

    protected function validateIdentifier($item)
    {
        // Ensure that the user provided an Identifier value.
        if (!$this->validateRequiredElement('Dublin Core', 'Identifier', $item, get_db()->getTable('Element'))) {
            $nextElementId = $this->getNextIdentifier();
            $item->addError('Identifier', "Value was blank and has been replaced with the next available Identifier $nextElementId.");
        }
    }

    protected function validateLocation($item, $elementTable)
    {
        // Make sure Country has a value if Location has a value.
        $locationElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Location');
        if (!empty($_POST['Elements'][$locationElement->id][0]['text'])) {
            $this->validateRequiredElement('Item Type Metadata', 'Country', $item, $elementTable);
        }
    }

    private function validateRequiredElement($elementSetName, $elementName, $item, $elementTable)
    {
        $element = $elementTable->findByElementSetNameAndElementName($elementSetName, $elementName);

        if (empty($_POST['Elements'][$element->id][0]['text'])) {
            $item->addError($elementName, 'Value required');
            return false;
        }

        return true;
    }

    protected function validateRequiredFields($item, $elementTable)
    {
        // Make sure that required fields have values.
        $this->validateRequiredElement('Dublin Core', 'Title', $item, $elementTable);
        $this->validateRequiredElement('Dublin Core', 'Type', $item, $elementTable);
        $this->validateRequiredElement('Dublin Core', 'Subject', $item, $elementTable);
        $this->validateRequiredElement('Dublin Core', 'Rights', $item, $elementTable);
        $this->validateRequiredElement('Item Type Metadata', 'Status', $item, $elementTable);
    }

    protected function validateStatus($item, $elementTable)
    {
        // Get the values of the Access DB and Status elements.
        $accessDBElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Access DB');
        $accessDBValue = $_POST['Elements'][$accessDBElement->id][0]['text'];
        $statusElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Status');
        $statusValue = $_POST['Elements'][$statusElement->id][0]['text'];

        // Make sure that the Status is not set to Accepted if the Access DB field is "Unconverted".
        if ($statusValue == 'Accepted' && $accessDBValue == 'Unconverted') {
            $item->addError('Status', "Status cannot be set to Accepted when Access DB is unconverted");
        }
        else {
            // Make sure that the Access DB field is not set for an item that did not come from Access.
            $this->validateAccessDB($item, $elementTable, $accessDBValue);
        }
    }

    protected function validateRestrictedElementValue($item, $elementName, $elementValue)
    {
        // Check that the element value contains none of the following:
        // - carriage return
        // - leading or trailing whitespace (includes tabs)
        // - n-dash or m-dash

        if ($elementValue != str_replace(array("\r", "\n"), '', $elementValue))
        {
            $item->addError($elementName, "Value contains a carriage return (possibly at the end)");
            return false;
        }

        if (strlen($elementValue) > strlen(trim($elementValue)))
        {
            $item->addError($elementName, "Value has leading or trailing spaces");
            return false;
        }

        $en_dash = html_entity_decode('&#x2013;', ENT_COMPAT, 'UTF-8');
        $em_dash = html_entity_decode('&#8212;', ENT_COMPAT, 'UTF-8');
        if ($elementValue != str_replace(array($en_dash, $em_dash), '', $elementValue))
        {
            $item->addError($elementName, "Value contains an en-dash or an em-dash, but only hyphen is allowed");
            return false;
        }

        return true;
    }

    protected function validateTitle($item, $elementTable)
    {
        $titleElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Title');
        $titleValue = $_POST['Elements'][$titleElement->id][0]['text'];

        if (!$this->validateRestrictedElementValue($item, 'Title', $titleValue))
        {
            // There was an error, so don't continue validating.
            return;
        }

        if (substr($titleValue, 0, 1) == "'")
        {
            $item->addError('Title', "A title cannot begin with a single quote. Use a double-quote instead.");
            return;
        }

        $typeElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Type');
        $typeValue = $_POST['Elements'][$typeElement->id][0]['text'];
        $isArticle = strpos($typeValue, "Article,") === 0;

        // Make sure that this item is not an article with the same title as another article.
        if ($isArticle)
        {
            // Get all items that have the same title.
            $element = get_db()->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Title');
            $duplicateItems = get_records('Item', array( 'advanced' => array( array('element_id' => $element->id, 'type' => 'is exactly', 'terms' => $titleValue ))));
            foreach ($duplicateItems as $duplicateItem)
            {
                if ($duplicateItem->id == $item->id)
                {
                    // Ignore the item we are comparing against.
                    continue;
                }
                if ($this->itemTypeIsArticle($duplicateItem))
                {
                    $item->addError('Title', "Another article exists with the same title as this article");
                    return;
                }
            }
        }
    }
}
