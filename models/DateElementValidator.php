<?php
class DateElementValidator extends ElementValidator
{
    public function hideStartEndDates($elementsBySet)
    {
        // Hide the Date Start and Date End elements when they both show the same value.
        $item = get_current_record('item');
        $dateStart = ItemMetadata::getElementTextFromElementName($item, array('Item Type Metadata', 'Date Start'));
        $dateEnd = ItemMetadata::getElementTextFromElementName($item, array('Item Type Metadata', 'Date End'));

        if ($dateStart == $dateEnd) {
            // Get the name of the item type metadata set to use as an index into the array of element sets.
            // Normally this isn't necessary, but it is when filtering elements.
            $itemTypeName = metadata($item, 'item type name');
            $itemTypeElementSetName = $itemTypeName . ' ' . ElementSet::ITEM_TYPE_NAME;

            // Remove the Date Start and Date End elements from the element set so they won't be displayed.
            unset($elementsBySet[$itemTypeElementSetName]['Date Start']);
            unset($elementsBySet[$itemTypeElementSetName]['Date End']);
        }

        return $elementsBySet;
    }

    protected function parseDate($date)
    {
        $date = strtok($date, " ");

        $matches = '';
        $year = "0000";
        $month = "01";
        $day = "01";

        $formatOk = true;

        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches))
        {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
        }
        elseif (preg_match("/^(\d{4})-(\d{2})$/", $date, $matches))
        {
            $year = $matches[1];
            $month = $matches[2];
        }
        elseif (preg_match("/^(\d{4})$/", $date, $matches))
        {
            $year = $matches[1];
        }
        else
        {
            $formatOk = false;
        }
        return array($year, $month, $day, $formatOk);
    }

    public function validateDates($item, $elementTable)
    {
        // Make sure Date Start and Date End have values if Date has a value.
        $dateElement = $elementTable->findByElementSetNameAndElementName('Dublin Core', 'Date');
        $dateStartElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Date Start');
        $dateEndElement = $elementTable->findByElementSetNameAndElementName('Item Type Metadata', 'Date End');

        $dateText = $_POST['Elements'][$dateElement->id][0]['text'];
        $dateStartText = $_POST['Elements'][$dateStartElement->id][0]['text'];
        $dateEndText = $_POST['Elements'][$dateEndElement->id][0]['text'];

        // Date, Date Start, and Date End are all empty.
        if (empty($dateText) && empty($dateStartText) && empty($dateEndText))
        {
            return;
        }

        list($dateYear, $month, $day, $formatOk) = $this->parseDate($dateText);
        if (!empty($dateText) && !$formatOk)
            return;

        list($dateStartYear, $month, $day, $formatOk) = $this->parseDate($dateStartText);
        if (!empty($dateStartText) && !$formatOk)
            return;

        list($dateEndYear, $month, $day, $formatOk) = $this->parseDate($dateEndText);
        if (!empty($dateEndText) && !$formatOk)
            return;

        if (empty($dateText))
        {
            if ($dateStartYear == $dateEndYear)
            {
                $item->addError('Dates', "When Date is empty, Date Start and Date End must each be set to a different year");
                return;
            }
        }
        else
        {
            if ($dateStartYear != $dateYear || $dateEndYear != $dateYear)
            {
                $item->addError('Dates', "When Date is set, Date Start and Date End must be set to the same year as Date");
                return;
            }
        }
    }

    public function validateElementDate(Item $item, $elementId, $elementName, $text)
    {
        list($year, $month, $day, $formatOk) = $this->parseDate($text);

        if ($formatOk && checkdate($month, $day, $year))
        {
            return true;
        }

        $this->addError($item, $elementName, __('Value must be in the form YYYY-MM-DD or YYYY-MM or YYYY.'));

        return true;
    }

    public function validateElementYear(Item $item, $elementId, $elementName, $text)
    {
        if (strlen($text) != 4 || !ctype_digit($text)) {
            $this->addError($item, $elementName, __('Value must be a year consisting of exactly four digits with no leading or trailing spaces.'));
        }

        return true;
    }
}