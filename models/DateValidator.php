<?php
class DateValidator
{
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