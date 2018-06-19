<?php
class DisplayFilter
{
    protected $checkboxFieldsData;
    protected $customCallback;

    public function __construct(&$filters, CustomCallback $customCallback)
    {
        $this->customCallback = $customCallback;
        $this->initializeCheckboxFields($filters);
        $this->initializeFilterFields($filters);
    }

    public function displayField($filterName, $item, $elementId, $text)
    {
        if (strpos($filterName, 'filterDisplayCheckbox') === 0)
        {
            $text = $this->displayFieldForCheckbox($elementId);
        }
        elseif (strpos($filterName, 'filterDisplayField') === 0)
        {
            $text = $this->displayFilteredTextFor($item, $elementId, $text);
        }

        return $text;
    }

    public function displayFieldForCheckbox($elementId)
    {
        $definition = $this->checkboxFieldsData[$elementId];
        return $definition['checked'];
    }

    public function displayFilteredTextFor($item, $elementId, $text)
    {
        $filteredText = $this->customCallback->performCallbackForElement(CustomCallback::CALLBACK_ACTION_FILTER, $item, $elementId, $text);
        return $filteredText;
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
        $definitions = ElementsConfig::getOptionDataForCallback();
        foreach ($definitions as $definition)
        {
            $elementName = $definition['name'];
            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            foreach ($definition['callbacks'] as $callback)
            {
                if ($callback['action'] == CustomCallback::CALLBACK_ACTION_FILTER)
                {
                    // Set up a call to be made when this element is displayed on a Show page.
                    $filters['filterDisplayField' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
                }
            }
        }
    }
}