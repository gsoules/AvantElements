<?php
class DateValidator
{
    public function hideStartEndYears($elementsBySet)
    {
        // Hide the Year Start and Year End elements when they both show the same value.

        $item = get_current_record('item');

        $yearStartElementName = CommonConfig::getOptionTextForYearStart();
        $yearEndElementName = CommonConfig::getOptionTextForYearEnd();

        $yearStart = ItemMetadata::getElementTextFromElementName($item, array('Item Type Metadata', $yearStartElementName));
        $yearEnd = ItemMetadata::getElementTextFromElementName($item, array('Item Type Metadata', $yearEndElementName));

        if ($yearStart == $yearEnd) {
            // Get the name of the item type metadata set to use as an index into the array of element sets.
            // Normally this isn't necessary, but it is when filtering elements.
            $itemTypeName = metadata($item, 'item type name');
            $itemTypeElementSetName = $itemTypeName . ' ' . ElementSet::ITEM_TYPE_NAME;

            // Remove the Year Start and Year End elements from the element set so they won't be displayed.
            unset($elementsBySet[$itemTypeElementSetName][$yearStartElementName]);
            unset($elementsBySet[$itemTypeElementSetName][$yearEndElementName]);
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

    public function validateDates(Item $item)
    {
        // Make sure Year Start and Year End have values if Date has a value.

        $yearStartElementName = CommonConfig::getOptionTextForYearStart();
        $yearEndElementName = CommonConfig::getOptionTextForYearEnd();

        $dateText = AvantCommon::getPostTextForElementName('Date');
        $yearStartText = AvantCommon::getPostTextForElementName($yearStartElementName);
        $yearEndText =  AvantCommon::getPostTextForElementName($yearEndElementName);

        // Date, Year Start, and Year End are all empty.
        if (empty($dateText) && empty($yearStartText) && empty($yearEndText))
        {
            return;
        }

        list($dateYear, $month, $day, $formatOk) = $this->parseDate($dateText);
        if (!empty($dateText) && !$formatOk)
            return;

        list($dateStartYear, $month, $day, $formatOk) = $this->parseDate($yearStartText);
        if (!empty($yearStartText) && !$formatOk)
            return;

        list($dateEndYear, $month, $day, $formatOk) = $this->parseDate($yearEndText);
        if (!empty($yearEndText) && !$formatOk)
            return;

        if (empty($dateText))
        {
            if ($dateStartYear == $dateEndYear)
            {
                AvantElements::addError($item, 'Dates', "When Date is empty, Year Start and Year End must each be set to a different year");
                return;
            }
        }
        else
        {
            if ($dateStartYear != $dateYear || $dateEndYear != $dateYear)
            {
                AvantElements::addError($item, 'Dates', "When Date is set, Year Start and Year End must be set to the same year as Date");
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

        AvantElements::addError($item, $elementName, __('Value must be in the form YYYY-MM-DD or YYYY-MM or YYYY.'));

        return true;
    }

    public function validateElementYear(Item $item, $elementId, $elementName, $text)
    {
        if (strlen($text) != 4 || !ctype_digit($text)) {
            AvantElements::addError($item, $elementName, __('Value must be a year consisting of exactly four digits with no leading or trailing spaces.'));
        }

        return true;
    }
}