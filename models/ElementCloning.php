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

    public function getCloneElementValues($elementName)
    {
        $values = array();

        if ($elementName != ItemMetadata::getIdentifierElementName())
        {
            $values = ItemMetadata::getAllElementTextsForElementName($this->cloneItem, $elementName);

            if ($elementName == ItemMetadata::getTitleElementName())
            {
                // Insert CLONED into the first title to remind the admin that this new item is a clone.
                $values[0] = __("--- DUPLICATE ---\n%s", $values[0]);
            }
        }
        return $values;
    }

    public static function emitCloneButton($item)
    {
        // Add a 'Clone' button to the sidebar. It will show up near the bottom which isn't where it's
        // supposed to go, but the jQuery for AvantElements will move it up under the other buttons.
        $itemId = $item->id;
        $url = $itemId ? 'items/add/' . $itemId : '.';
        $url = WEB_ROOT . '/admin/' . $url;
        $label = __('Duplicate This Item');
        echo "<a id='clone-button' href='$url' target='_blank' class='big blue button'>$label</a>";
    }
}