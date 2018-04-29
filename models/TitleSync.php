<?php
class TitleSync
{
    protected $originalTitle;

    public function syncTitles($item)
    {
        $currentTitle = ItemMetadata::getItemTitle($item, false);
        if ($currentTitle == $this->originalTitle)
        {
            // The title hasn't changed. There's nothing to sync.
            return;
        }

        $titleSyncData = ElementsConfig::getOptionDataForTitleSync();
        foreach ($titleSyncData as $elementId => $elementName)
        {
            // Update elements that that need to stay in sync with this item's Title.
            $this->updateElementText($elementId,  $this->originalTitle, $currentTitle);
        }
    }

    public function setCurrentTitle($item)
    {
        $this->originalTitle = ItemMetadata::getItemTitle($item, false);
    }

    protected function updateElementText($elementId, $oldTitleText, $newTitleText)
    {
        // Get all the elements that have the old title value.
        $elements = ItemMetadata::getElementsByValue($elementId, $oldTitleText);

        foreach ($elements as $element)
        {
            // Update the element with the new title value.
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