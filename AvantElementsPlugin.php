<?php

class AvantElementsPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $elementFilters;
    protected $itemValidator;
    protected $linkBuilder;
    protected $titleSync;

    protected $_hooks = array(
        'admin_head',
        'admin_footer',
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

        $this->elementFilters = new ElementFilters();
        $this->itemValidator = new ItemValidator();
        $this->linkBuilder = new LinkBuilder($this->_filters);
        $this->titleSync = new TitleSync();
    }

    public function __call($name, $arguments)
    {
        // Handle filter requests from the LinkBuilder for filterLinkImplicit and filterLinkExternal.
        $result = null;

        if (strpos($name, 'filterLink') === 0)
        {
            $result = $this->linkBuilder->buildLink($name, $arguments);
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
        return $this->elementFilters->filterElementSave($this->itemValidator, $text, $args);
    }

    public function filterElementValidate($isValid, $args)
    {
        return $this->elementFilters->filterElementValidate($this->itemValidator, $isValid, $args);
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
        $this->titleSync->syncTitles($item);
    }

    public function hookBeforeSaveItem($args)
    {
        $item = $args['record'];
        $this->itemValidator->beforeSaveItem($item);
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
