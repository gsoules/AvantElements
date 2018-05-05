<?php
class ElementValidator
{
    const CALLBACK_ACTION_DEFAULT = 'default';
    const CALLBACK_ACTION_SAVE = 'save';
    const CALLBACK_ACTION_VALIDATE = 'validate';

    const VALIDATION_TYPE_DATE = 'date';
    const VALIDATION_TYPE_REQUIRED = 'required';
    const VALIDATION_TYPE_SIMPLE_TEXT = 'simple-text';
    const VALIDATION_TYPE_YEAR = 'year';

    protected $callbacks;
    protected $validationOptionData;

    public function __construct()
    {
        $this->validationOptionData = ElementsConfig::getOptionDataForValidation();
        $this->callbacks = ElementsConfig::getOptionDataForCallback();
    }

    public function afterSaveItem($item)
    {
        $this->performCallbackAfterSave($item);
    }

    public function beforeSaveItem($item)
    {
        $this->validateRequiredElements($item);

        $dateValidator = new DateValidator();
        $dateValidator->validateDates($item);

        $this->performCallbackBeforeSave($item);
    }

    protected function constructCallbackFunctionName(Item $item, $elementId, $callback, $callbackAction)
    {
        if ($callback['action'] != $callbackAction)
        {
            return '';
        }

        $callbackFunctionName = $callback['class'] . '::' . $callback['function'];

        if (!is_callable($callbackFunctionName))
        {
            $target = $elementId == 0 ? '<item>' : ItemMetadata::getElementNameFromId($elementId);
            AvantElements::addError($item, $target, __('Callback %s function \'%s\' is not callable.', $callback['action'], $callbackFunctionName));
            $callbackFunctionName = '';
        }

        return $callbackFunctionName;
    }

    public function filterElementText($item, $elementId, $text)
    {
        if (strlen($text) == 0)
        {
            // The string has no content. Note use of strlen() instead of empty()
            // to safely detect that a boolean value "0" is not considered empty.
            return self::performCallbackForDefault($item, $elementId);
        }

        if ($this->hasValidationDefinitionFor($elementId, self::VALIDATION_TYPE_YEAR))
        {
            $text = trim($text);
        }

        if ($this->hasValidationDefinitionFor($elementId, self::VALIDATION_TYPE_SIMPLE_TEXT))
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

    public function getCallbackDefaultElementText($item, $elementId)
    {
        $text = '';
        $callbackFunctionName = $this->getCallbackFunctionName($item, $elementId, ElementValidator::CALLBACK_ACTION_DEFAULT);
        if (!empty($callbackFunctionName))
        {
            $text = call_user_func($callbackFunctionName, $item);
        }
        return $text;
    }

    protected function getCallbackFunctionName($item, $elementId, $callbackAction)
    {
        foreach ($this->callbacks as $callbackElementId => $callbackDefinition)
        {
            if ($elementId != $callbackElementId)
            {
                continue;
            }
            foreach ($callbackDefinition['callbacks'] as $callback)
            {
                if ($callback['action'] != $callbackAction)
                {
                    continue;
                }
                return $this->constructCallbackFunctionName($item, $elementId, $callback, $callbackAction);
            }
        }
        return '';
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

    public function performCallbackAfterSave($item)
    {
        $callbackFunctionName = $this->getCallbackFunctionName($item, 0, self::CALLBACK_ACTION_SAVE);
        if (!empty($callbackFunctionName))
        {
            call_user_func($callbackFunctionName, $item);
        }
    }

    public function performCallbackBeforeSave($item)
    {
        $callbackFunctionName = $this->getCallbackFunctionName($item, 0, self::CALLBACK_ACTION_VALIDATE);
        if (!empty($callbackFunctionName))
        {
            call_user_func($callbackFunctionName, $item);
        }
    }

    public function performCallbackForDefault($item, $elementId)
    {
        $callbackFunctionName = $this->getCallbackFunctionName($item, $elementId, self::CALLBACK_ACTION_DEFAULT);
        $text = '';
        if (!empty($callbackFunctionName))
        {
            $text =call_user_func($callbackFunctionName, $item, $elementId);
        }
        return $text;
    }

    public function performCallbackValidation($item, $elementId, $elementName, $text)
    {
        $callbackFunctionName = $this->getCallbackFunctionName($item, $elementId, self::CALLBACK_ACTION_VALIDATE);
        if (!empty($callbackFunctionName))
        {
            call_user_func($callbackFunctionName, $item, $elementId, $elementName, $text);
        }
    }

    public function validateElementText(Item $item, $elementId, $elementName, $text)
    {
        $dateValidator = new DateValidator();

        if ($this->hasValidationDefinitionFor($elementId, self::VALIDATION_TYPE_DATE))
        {
            $dateValidator->validateElementDate($item, $elementName, $text);
        }

        if ($this->hasValidationDefinitionFor($elementId, self::VALIDATION_TYPE_YEAR))
        {
            $dateValidator->validateElementYear($item, $elementName, $text);
        }
    }

    protected function validateRequiredElement($item, $elementId, $elementName)
    {
        if (!AvantCommon::elementHasPostedValue($elementId))
        {
            AvantElements::addError($item, $elementName, __('A value is required.'));
        }
    }

    public function validateRequiredElements(Item $item)
    {
        $definitions = $this->getValidationDefinitionsFor(self::VALIDATION_TYPE_REQUIRED);
        foreach ($definitions as $elementId => $definition)
        {
            $this->validateRequiredElement($item, $elementId, $definition['name']);
        }
    }
}