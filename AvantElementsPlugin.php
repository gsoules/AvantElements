<?php

class AvantElementsPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $customCallback;
    protected $displayFilter;
    protected $elementFilters;
    protected $elementValidator;
    protected $linkBuilder;
    protected $titleSync;

    protected $_hooks = array(
        'admin_footer',
        'admin_head',
        'admin_items_show_sidebar',
        'after_save_item',
        'before_save_item',
        'config',
        'config_form',
        'define_routes',
        'initialize',
        'public_head'
    );

    protected $_filters = array(
        'display_elements'
    );

    public function __construct()
    {
        parent::__construct();

        if (AvantCommon::isSearchRequest())
        {
            // Don't spend the execution time to construct this class since it's not used for search requests.
            return;
        }

        $this->customCallback = new CustomCallback();
        $this->elementValidator = new ElementValidator($this->customCallback);
        $this->displayFilter = new DisplayFilter($this->_filters, $this->customCallback);
        $this->elementFilters = new ElementFilters($this->customCallback, $this->elementValidator);
        $this->linkBuilder = new LinkBuilder();
        $this->linkBuilder->initializeFilters($this->_filters);
        $this->titleSync = new TitleSync();
    }

    public function __call($filterName, $args)
    {
        // Handle filter requests from the LinkBuilder for filterLinkImplicit and filterLinkExternal.
        $result = null;
        $item = $args[1]['record'];
        $elementId = $args[1]['element_text']['element_id'];
        $text = $args[0];

        if (strpos($filterName, 'filterLink') === 0)
        {
            $result = $this->linkBuilder->buildLink($filterName, $elementId, $text);
        }
        elseif (strpos($filterName, 'filterDisplay') === 0)
        {
            $result = $this->displayFilter->displayField($filterName, $item, $elementId, $text);
        }
        return $result;
    }

    public function filterDisplayElements($elementsBySet)
    {
        return $this->elementFilters->filterDisplayElements($elementsBySet);
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
        return $this->elementFilters->filterElementSave($text, $args);
    }

    public function filterElementValidate($isValid, $args)
    {
        return $this->elementFilters->filterElementValidate($args);
    }

    public function hookAdminFooter($args)
    {
        $item = get_current_record('item', false);
        if (!$item)
            return;

        // The code below should only get called when editing an item, but for now it gets invoked
        // even when just viewing an item. It needs to get moved to a method that only gets called
        // when editing an item. For now, it's harmless as-is.
        $elementIds = ElementSuggest::getIdsForSuggestElements();
        echo get_view()->partial('/avantelements-script.php', array('identifier' => ItemMetadata::getItemIdentifier($item) , 'fields' => $elementIds));
    }

    public function hookAdminHead($args)
    {
        queue_css_file('avantelements-admin');
        queue_css_file('select2.min');
        queue_js_file('select2.min');
        AvantElements::emitAdminCss();
    }

    public function hookAdminItemsShowSidebar($args)
    {
        $item = $args['item'];
        ElementCloning::emitCloneButton($item);
    }

    public function hookAfterSaveItem($args)
    {
        if (!AvantCommon::userClickedSaveChanges())
        {
            // Don't perform validation for a save that is done programmatically such as when batch editing.
            return;
        }

        $item = $args['record'];
        $this->elementValidator->afterSaveItem($item);
        $this->titleSync->syncTitles($item);
    }

    public function hookBeforeSaveItem($args)
    {
        if (!AvantCommon::userClickedSaveChanges())
        {
            // Don't perform validation for a save that is done programmatically such as when batch editing.
            return;
        }

        $item = $args['record'];
        $this->elementValidator->beforeSaveItem($item);

        if (AvantElements::itemHasErrors($item))
        {
            return;
        }

        $this->titleSync->setCurrentTitle($item);
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

    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');

        if (AvantCommon::isSearchRequest())
        {
            // Don't spend the execution time to construct this class since it's not used for search requests.
            return;
        }

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
}
