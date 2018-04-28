<?php
class ItemValidator
{
    protected $callbacks;
    protected $validationOptionData;

    public function __construct()
    {
        $this->validationOptionData = ElementsConfig::getOptionDataForValidation();
        $this->callbacks = ElementsConfig::getOptionDataForCallback();
    }

    public static function addError(Item $item, $elementName, $message)
    {
        $item->addError($elementName, $message);
    }

    protected function constructCallbackFunctionName(Item $item, $target, $callback, $callbackType)
    {
        if ($callback['type'] != $callbackType)
        {
            return '';
        }

        $callbackFunctionName = $callback['class'] . '::' . $callback['function'];

        if (!is_callable($callbackFunctionName))
        {
            AvantElements::addError($item, $target, __('Callback %s function \'%s\' is not callable.', $callback['type'], $callbackFunctionName));
            $callbackFunctionName = '';
        }

        return $callbackFunctionName;
    }

    public function elementHasPostedValue($elementId)
    {
        return !empty($_POST['Elements'][$elementId][0]['text']);
    }

    public function filterElementText($elementId, $text)
    {
        if ($this->hasValidationDefinitionFor($elementId, 'year'))
        {
            $text = trim($text);
        }

        if ($this->hasValidationDefinitionFor($elementId, 'restricted'))
        {
            $text = $this->filterRestrictedText($text);
        }
        return $text;
    }

    protected function filterRestrictedText($text)
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

    public function getItemCallbackFunctionName(Item $item)
    {
        if (!isset($this->callbacks[0]))
            return '';

        $callbackFunctionName = $this->constructCallbackFunctionName($item, '<item>', $this->callbacks[0]['callbacks'][0], 'validate');
        return $callbackFunctionName;
    }

    protected function getValidationDefinitionsFor($validationType)
    {
        $definitionsForType = array();

        $definitions = ElementsConfig::getOptionDataForValidation();
        foreach ($definitions as $elementId => $definition)
        {
            foreach ($definition['args'] as $argName)
            {
                if ($argName == $validationType)
                {
                    $definitionsForType[$elementId] = $definition;
                }
            }
        }
        return $definitionsForType;
    }

    protected function hasValidationDefinitionFor($elementId, $validationType)
    {
        if (!isset($this->validationOptionData[$elementId]))
        {
            // This element has no validation definitions.
            return false;
        }
        $validationTypes = $this->validationOptionData[$elementId]['args'];
        return in_array($validationType, $validationTypes);
    }

    public function performCallbackValidation($item, $elementId, $elementName, $text)
    {
        foreach ($this->callbacks as $callbackElementId => $callbackDefinition)
        {
            if ($elementId != $callbackElementId)
            {
                continue;
            }
            foreach ($callbackDefinition['callbacks'] as $callback)
            {
                $callbackFunctionName = $this->constructCallbackFunctionName($item, $elementName, $callback, 'validate');
                if (!empty($callbackFunctionName))
                {
                    call_user_func($callbackFunctionName, $item, $elementId, $elementName, $text);
                }
            }
        }
    }

    public function validateElementText(Item $item, $elementId, $elementName, $text)
    {
        $dateValidator = new DateValidator();

        if ($this->hasValidationDefinitionFor($elementId, 'date'))
        {
            $dateValidator->validateElementDate($item, $elementId, $elementName, $text);
        }

        if ($this->hasValidationDefinitionFor($elementId, 'year'))
        {
            $dateValidator->validateElementYear($item, $elementId, $elementName, $text);
        }
    }

    protected function validateRequiredElement($item, $elementId, $elementName)
    {
        if (!$this->elementHasPostedValue($elementId))
        {
            AvantElements::addError($item, $elementName, __('A value is required.'));
        }
    }

    public function validateRequiredElements(Item $item)
    {
        $definitions = $this->getValidationDefinitionsFor('required');
        foreach ($definitions as $elementId => $definition)
        {
            $this->validateRequiredElement($item, $elementId, $definition['name']);
        }
    }
}