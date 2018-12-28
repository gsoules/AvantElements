<?php
class DateValidator
{
    public function hideStartEndYears($item, $elementsBySet)
    {
        // Hide the Year Start and Year End elements when they both show the same value.

        $yearStartElementName = CommonConfig::getOptionTextForYearStart();
        $yearEndElementName = CommonConfig::getOptionTextForYearEnd();

        $yearStart = ItemMetadata::getElementTextForElementName($item,  $yearStartElementName);
        $yearEnd = ItemMetadata::getElementTextForElementName($item, $yearEndElementName);

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

    public function parseDate($date)
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

    public function validateDateCombinations(Item $item)
    {
        // Make sure Year Start and Year End have proper values if Date has a value.
        $yearStartElementName = CommonConfig::getOptionTextForYearStart();
        $yearEndElementName = CommonConfig::getOptionTextForYearEnd();
        if (empty($yearStartElementName) || empty($yearStartElementName))
        {
            // This installation is not configured to use year start/end.
            return;
        }

        $dateText = AvantCommon::getPostTextForElementName('Date');
        $yearStartText = AvantCommon::getPostTextForElementName($yearStartElementName);
        $yearEndText =  AvantCommon::getPostTextForElementName($yearEndElementName);

        // Date, Year Start, and Year End are all empty.
        if (empty($dateText) && empty($yearStartText) && empty($yearEndText))
        {
            // Date, Year Start, and Year End are all empty. Nothing to validate.
            return;
        }

        list($dateYear, $month, $day, $formatOk) = $this->parseDate($dateText);
        if (!empty($dateText) && !$formatOk)
        {
            // The Date is not valid. The error will be reported by the Date validator.
            return;
        }

        list($dateStartYear, $month, $day, $formatOk) = $this->parseDate($yearStartText);
        if (!empty($yearStartText) && !$formatOk)
        {
            // The start year is set, but is not valid. The error will be reported by the validator for the start year.
            return;
        }

        list($dateEndYear, $month, $day, $formatOk) = $this->parseDate($yearEndText);
        if (!empty($yearEndText) && !$formatOk)
        {
            // The end year is set, but is not valid. The error will be reported by the validator for the end year.
            return;
        }

        if (empty($dateText))
        {
            if ($dateStartYear == $dateEndYear)
            {
                AvantElements::addError($item, 'Dates', __("When Date is empty, Year Start and Year End must each be set to a different year"));
                return;
            }
            if ($dateStartYear > $dateEndYear)
            {
                AvantElements::addError($item, 'Dates', __("Year Start must be less than Year End"));
                return;
            }
        }
        else
        {
            if (empty($yearStartText) && empty($yearEndText))
            {
                // The Date is set, but both start and end year are empty. Give them default values.
                $dateStartElementId = ItemMetadata::getElementIdForElementName($yearStartElementName);
                AvantCommon::setPostTextForElementId($dateStartElementId, $dateYear);
                $dateEndElementId = ItemMetadata::getElementIdForElementName($yearEndElementName);
                AvantCommon::setPostTextForElementId($dateEndElementId, $dateYear);

                // Notify the admin about the default values. Doing this also causes the page to post again which works
                // around a problem whereby just setting the post text above is not causing the values to get saved.
                // If you want to eliminate the warning, you'll have to figure out why the values don't get saved. Note
                // that they will get saved if the page has an error, but that's because it posts again. For now, this
                // warning is preferable to having to set the data start/end values manually.
                $buttonName = empty($item->id) ? _("Add Item") : __("Save Changes");
                AvantElements::addError($item, 'Date', __("$yearStartElementName and $yearEndElementName cannot be empty when Date is set. Click $buttonName to set them to $dateYear."));
            }
            else if ($dateStartYear != $dateYear || $dateEndYear != $dateYear)
            {
                AvantElements::addError($item, 'Dates', __("When Date is set, Year Start and Year End must be set to the same year as Date"));
                return;
            }
        }
    }

    public function validateElementDate(Item $item, $elementName, $text)
    {
        list($year, $month, $day, $formatOk) = $this->parseDate($text);

        if ($formatOk && checkdate($month, $day, $year))
        {
            return;
        }

        AvantElements::addError($item, $elementName, __('Value must be in the form YYYY-MM-DD or YYYY-MM or YYYY.'));
    }

    public function validateElementYear(Item $item, $elementName, $text)
    {
        if (strlen($text) != 4 || !ctype_digit($text))
        {
            AvantElements::addError($item, $elementName, __('Value must be a year consisting of exactly four digits with no leading or trailing spaces.'));
        }
    }
}