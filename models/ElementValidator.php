<?php
class ElementValidator
{
    protected $validationOptionData;

    public function __construct()
    {
        $this->validationOptionData = ElementsConfig::getOptionDataForValidation();
    }

    public static function addError(Item $item, $elementName, $message)
    {
        $item->addError($elementName, $message);
    }

    public function elementHasPostedValue($elementId)
    {
        return !empty($_POST['Elements'][$elementId][0]['text']);
    }

    public function postProcessElementText($elementId, $text)
    {
        if (!array_key_exists($elementId, $this->validationOptionData))
        {
            // This element has no sanitation requirements.
            return $text;
        }

        $definition = $this->validationOptionData[$elementId];

        foreach ($definition['args'] as $argName => $arg)
        {
            if ($arg == false)
            {
                continue;
            }

            switch ($argName)
            {
                case 'restricted':
                    $text = $this->sanitizeText($text);
                    break;
            }
        }
        return $text;
    }

    protected function sanitizeText($text)
    {
        // Remove carriage returns and tabs.
        $text = str_replace(array("\r", "\n", "\t"), '', $text);

        // Trim away leading or trailing whitespace, carriage returns, and tabs.
        $text = trim($text);

        // Replace en or em dashes with hyphens.
        $en_dash = html_entity_decode('&#x2013;', ENT_COMPAT, 'UTF-8');
        $em_dash = html_entity_decode('&#8212;', ENT_COMPAT, 'UTF-8');
        $text = str_replace(array($en_dash, $em_dash), '-', $text);

        return $text;
    }

    public function validateElementText(Item $item, $elementId, $elementName, $text)
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
                    case 'date':
                        $validator = new DateElement();
                        $isValid = $validator->validateElementDate($item, $elementId, $elementName, $text);
                        break;

                    case 'year':
                        $validator = new DateElement();
                        $isValid = $validator->validateElementYear($item, $elementId, $elementName, $text);
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

    public function validateElementBeforeSave(Item $item, $elementTable)
    {
        $definitions = ElementsConfig::getOptionDataForValidation();
        foreach ($definitions as $elementId => $definition)
        {
            $elementName = $definition['name'];

            foreach ($definition['args'] as $argName => $arg)
            {
                if ($arg == false)
                {
                    continue;
                }

                switch ($argName)
                {
                    case 'required':
                        $this->validateRequiredElement($item, $elementId, $elementName);
                        break;

                    case 'unique':
                        break;
                }
            }
        }
    }

    protected function validateRequiredElement($item, $elementId, $elementName)
    {
        if (!$this->elementHasPostedValue($elementId))
        {
            self::addError($item, $elementName, __('A value is required.'));
        }
    }
}