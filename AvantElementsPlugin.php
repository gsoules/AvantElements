<?php

class AvantElementsPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $dateValidator;
    protected $elementFilters;
    protected $itemValidator;
    protected $linkBuilder;
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

        $this->elementFilters = new ElementFilters();
        $this->itemValidator = new ItemValidator();
        $this->dateValidator = new DateValidator();
        $this->linkBuilder = new LinkBuilder($this->_filters);
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
        return $this->elementFilters->filterDisplayElements($this->dateValidator, $elementsBySet);
    }

    public function filterElementForm($components, $args)
    {
        return $this->elementFilters->filterElementForm($components, $args);
    }

    public function filterElementInput($components, $args)
    {
        return $this->elementFilters->filterElementInput($components, $args);
    }

    public function filterElementSave($text, $args)
    {
        return $this->elementFilters->filterElementSave($this->itemValidator, $text, $args);
    }

    public function filterElementValidate($isValid, $args)
    {
        return $this->elementFilters->filterElementValidate($this->itemValidator, $isValid, $args);
    }

    protected function getTitleTexts($item)
    {
        $elementTable = get_db()->getTable('Element');
        $titleElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Title');
        return $item->getElementTextsByRecord($titleElement);
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

        $this->itemValidator->validateRequiredElements($item);
        $this->dateValidator->validateDates($item);
        $this->titleTextsBeforeSave = $this->getTitleTexts($item);
    }

    public function hookConfig()
    {
        ElementsConfig::saveConfiguration();
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

            // Add filters that get called after the user clicks the Edit button, but before the edit form is
            // displayed. These filters can alter the form e.g. to hide or show buttons or set text box widths.
            add_filter(array('ElementForm', 'Item', $set, $name), array($this, 'filterElementForm'));
            add_filter(array('ElementInput', 'Item', $set, $name), array($this, 'filterElementInput'));

            // Add filters that get called after the user clicks the Save button, but before the item's elements are
            // written to the database. These filters can display errors and thereby prevent the save from occurring.
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

    private function removeAddInputButton($elementId, $components)
    {
        $allowAddInputButton = array_key_exists($elementId, $this->multiInputElements);

        if (!$allowAddInputButton)
        {
            $components['add_input'] = false;
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
}
