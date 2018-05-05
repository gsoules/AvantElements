<?php
class AvantElements
{
    public static function addError(Item $item, $elementName, $message)
    {
        $item->addError($elementName, $message);
    }

    public static function itemHasErrors($item)
    {
        $errors = $item->getErrors()->get();
        return count($errors) > 0;
    }

    public function orderElementsForDisplay($elementSetsForDisplay)
    {
        $elementsData = ElementsConfig::getOptionDataForDisplayOrder();
        $displayOrder = array();
        foreach ($elementsData as $elementName)
        {
            $displayOrder[$elementName] = null;
        }

        // Copy the elements from the element sets (Dublin Core and others) into the ordered array.
        foreach ($elementSetsForDisplay as $elementSet)
        {
            foreach ($elementSet as $elementName => $elementInfo)
            {
                $displayOrder[$elementName] = $elementInfo;
            }
        }

        // Create another array that excludes any empty elements.
        $elementSet = array();
        foreach ($displayOrder as $elementName => $elementInfo)
        {
            if (empty($elementInfo))
                continue;
            $elementSet[$elementName] = $elementInfo;
        }

        return $elementSet;
    }
}