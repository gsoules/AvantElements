<?php
class ElementValidator
{
    const CALLBACK_ACTION_DEFAULT = 'default';
    const CALLBACK_ACTION_FILTER = 'filter';
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
        $dateValidator->validateDateCombinations($item);

        $this->performCallbackBeforeSave($item);
    }

    protected function callUserFunction($callbackFunctionName, $item, $elementId = 0, $text = '')
    {
        return call_user_func($callbackFunctionName, $item, $elementId, $text);
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

    public function getCallbackDefaultElementText($item, $elementId)
    {
        $text = '';
        $callbackFunctionName = $this->getCallbackFunctionName($item, $elementId, ElementValidator::CALLBACK_ACTION_DEFAULT);
        if (!empty($callbackFunctionName))
        {
            $text = $this->callUserFunction($callbackFunctionName, $item, $elementId, $text);
        }
        return $text;
    }

    protected function getCallbackFunctionName($item, $elementId, $callbackAction)
    {
        foreach ($this->callbacks as $callbackElementId => $definition)
        {
            if ($elementId != $callbackElementId)
            {
                continue;
            }
            foreach ($definition['callbacks'] as $callback)
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

    public function getCallbackFunctionText($callbackAction, $item, $elementId, $text = '')
    {
        $callbackFunctionName = $this->getCallbackFunctionName($item, $elementId ,$callbackAction);
        if (!empty($callbackFunctionName))
        {
            $text = $this->callUserFunction($callbackFunctionName, $item, $elementId, $text);
        }
        return $text;
    }

    public function getValidationDefinitionsFor($validationType)
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

    public function hasValidationDefinitionFor($elementId, $validationType)
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
            $this->callUserFunction($callbackFunctionName, $item, 0, '');
        }
    }

    public function performCallbackBeforeSave($item)
    {
        $callbackFunctionName = $this->getCallbackFunctionName($item, 0, self::CALLBACK_ACTION_VALIDATE);
        if (!empty($callbackFunctionName))
        {
            $this->callUserFunction($callbackFunctionName, $item, 0, '');
        }
    }

    public function performCallbackForDefault($item, $elementId)
    {
        $callbackFunctionName = $this->getCallbackFunctionName($item, $elementId, self::CALLBACK_ACTION_DEFAULT);
        $text = '';
        if (!empty($callbackFunctionName))
        {
            $text = $this->callUserFunction($callbackFunctionName, $item, $elementId, $text);
        }
        return $text;
    }

    public function performCallbackValidation($item, $elementId, $text)
    {
        $callbackFunctionName = $this->getCallbackFunctionName($item, $elementId, self::CALLBACK_ACTION_VALIDATE);
        if (!empty($callbackFunctionName))
        {
            $this->callUserFunction($callbackFunctionName, $item, $elementId, $text);
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