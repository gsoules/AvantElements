<?php
class ElementCloning
{
    protected $cloneItem;
    protected $cloning;
    protected $initialized;

    public function __construct()
    {
        $this->initialized = false;
    }

    public function cloning()
    {
        if (!$this->initialized)
        {
            // Get the request to this page to see if it's to Add a new item AND that an item Id is in the request.
            // Normally an Add request provides no Id, but when cloning, the Id is for the item to be cloned.
            // This can't be done in the constructor because the request object is not available at that time.
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $cloneItemId = $request->getParam('id');
            $this->cloning = $request->getParam('action') == 'add' && !empty($cloneItemId);

            if ($this->cloning)
            {
                // Get the item to be cloned.
                $this->cloneItem = ItemMetadata::getItemFromId($cloneItemId);
            }

            $this->initialized = true;
        }
        return $this->cloning;
    }

    public function cloneElementValue($elementId, $elementSetName, $elementName, $components)
    {
        if ($elementName == ItemMetadata::getIdentifierElementName())
        {
            // Don't clone the item's unique identifier.
            return $components;
        }

        $value = ItemMetadata::getElementTextFromElementName($this->cloneItem, array($elementSetName, $elementName));

        if ($elementName == ItemMetadata::getTitleElementName())
        {
            // Insert CLONED into the title to remind the admin that this new item is a clone.
            $value = __('CLONE OF: %s', $value);
        }

        if (!empty($value))
        {
            // Replace the elements input HTML with a simple text element that contains the cloned value.
            // This is much simpler than parsing TextAreas and Select lists to insert the value.
            $components['inputs'] = "<div class='input-block'><div class='input'><input type='text' name='Elements[$elementId][0][text]' id='Elements-$elementId-0-text' value='$value' style='width:400px;'></div></div>";
        }

        return $components;
    }

}