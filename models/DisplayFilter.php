<?php
class DisplayFilter
{
    protected $checkboxFieldsData;

    public function __construct(&$filters)
    {
        $this->initializeCheckboxFields($filters);
    }

    public function displayField($filterName, $arguments)
    {
        $text = $arguments[0];
        $elementId = $arguments[1]['element_text']['element_id'];

        if (strpos($filterName, 'filterDisplayCheckbox') === 0)
        {
            $text = $this->displayFieldForCheckbox($text, $elementId);
        }

        return $text;
    }

    public function displayFieldForCheckbox($text, $elementId)
    {
        $definition = $this->checkboxFieldsData[$elementId];
        return (bool)$text ? $definition['checked'] : $definition['unchecked'];
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
}