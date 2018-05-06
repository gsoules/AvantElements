<?php
class DisplayFilter
{
    protected $checkboxFieldsData;
    protected $elementValidator;
    protected $filterFieldsCallbacks;

    public function __construct(&$filters, ElementValidator $elementValidator)
    {
        $this->elementValidator = $elementValidator;
        $this->initializeCheckboxFields($filters);
        $this->initializeFilterFields($filters);
    }

    public function displayField($filterName, $item, $elementId, $text)
    {
        if (strpos($filterName, 'filterDisplayCheckbox') === 0)
        {
            $text = $this->displayFieldForCheckbox($elementId, $text);
        }
        elseif (strpos($filterName, 'filterDisplayField') === 0)
        {
            $text = $this->displayFilteredTextFor($item, $elementId, $text);
        }

        return $text;
    }

    public function displayFieldForCheckbox($elementId, $text)
    {
        $definition = $this->checkboxFieldsData[$elementId];
        return (bool)$text ? $definition['checked'] : $definition['unchecked'];
    }

    public function displayFilteredTextFor($item, $elementId, $text)
    {
        return $this->elementValidator->filterElementTextBeforeDisplay($item, $elementId, $text);
    }

    public function initializeCheckboxFields(&$filters)
    {
        $this->checkboxFieldsData = ElementsConfig::getOptionDataForCheckboxField();
        foreach ($this->checkboxFieldsData as $definition)
        {
            $elementName = $definition['name'];
            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                // Set up a call to be made when this element is displayed on a Show page.
                $filters['filterDisplayCheckbox' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }

    public function initializeFilterFields(&$filters)
    {
        $this->filterFieldsCallbacks = ElementsConfig::getOptionDataForCallback();
        foreach ($this->filterFieldsCallbacks as $callbackElementId => $definition)
        {
            $elementName = $definition['name'];
            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            foreach ($definition['callbacks'] as $callback)
            {
                if ($callback['action'] == ElementValidator::CALLBACK_ACTION_FILTER)
                {
                    // Set up a call to be made when this element is displayed on a Show page.
                    $filters['filterDisplayField' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
                }
            }
        }
    }
}