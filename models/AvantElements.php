<?php
class AvantElements
{
    protected $validationOptionData;

    public function __construct()
    {
       $this->validationOptionData = ElementsOptions::getOptionDataForValidation();
    }

    private function addError(Item $item, $elementName, $message)
    {
        $item->addError($elementName, $message);
    }

    public function orderElementsForDisplay($elementSetsForDisplay)
    {
        $elementsData = ElementsOptions::getOptionDataForDisplayOrder();
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

    public function validateElement(Item $item, $elementId, $elementName, $text)
    {
        $isValid = true;

        if (array_key_exists($elementId, $this->validationOptionData))
        {
            $definition = $this->validationOptionData[$elementId];

            foreach ($definition['args'] as $argName => $arg)
            {
                if ($arg == false)
                {
                    continue;
                }

                switch ($argName)
                {
//                    case 'required':
//                        break;
//
//                    case 'unique':
//                        break;

                    case 'date':
                        $isValid = $this->validateElementDate($item, $elementId, $elementName, $text);
                        break;

                    case 'year':
                        $isValid = $this->validateElementYear($item, $elementId, $elementName, $text);
                        break;
                }

                if ($isValid)
                {
                    break;
                }
            }
        }

        return true;
    }

    protected function validateElementDate(Item $item, $elementId, $elementName, $text)
    {
        list($year, $month, $day, $formatOk) = $this->parseDate($text);

        if ($formatOk && checkdate($month, $day, $year))
        {
            return true;
        }

        $this->addError($item, $elementName, __('Value must be in the form YYYY-MM-DD or YYYY-MM or YYYY.'));

        return true;
    }

    protected function validateElementYear(Item $item, $elementId, $elementName, $text)
    {
        if (strlen($text) != 4 || !ctype_digit($text)) {
            $this->addError($item, $elementName, __('Value must be a year consisting of exactly four digits with no leading or trailing spaces.'));
        }

        return true;
    }
}