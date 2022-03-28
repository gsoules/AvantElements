<?php
class ElementValidator
{
    const VALIDATION_TYPE_ACCEPT_NONE = 'accept-none';
    const VALIDATION_TYPE_DATE = 'date';
    const VALIDATION_TYPE_REQUIRED = 'required';
    const VALIDATION_TYPE_SIMPLE_TEXT = 'simple-text';
    const VALIDATION_TYPE_YEAR = 'year';
	const VALIDATION_TYPE_NUMERIC = 'numeric';
	const VALIDATION_TYPE_LOWER_CASE = 'lower-case';
	const VALIDATION_TYPE_UPPER_CASE = 'upper-case';
    const VALIDATION_NONE = 'none';

    protected $customCallback;
    protected $validationOptionData;

    public function __construct(CustomCallback $customCallback)
    {
        $this->customCallback = $customCallback;
        $this->validationOptionData = ElementsConfig::getOptionDataForValidation();
    }

    public function afterSaveItem($item)
    {
        $this->customCallback->performCallbackForItem(CustomCallback::CALLBACK_ACTION_SAVE, $item);
    }

    public function beforeSaveItem($item)
    {
        $this->validateRequiredElements($item);
        $this->customCallback->performCallbackForItem(CustomCallback::CALLBACK_ACTION_VALIDATE, $item);
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

        if ($this->hasValidationDefinitionFor($elementId, self::VALIDATION_TYPE_NUMERIC))
        {
            $this->validateNumericElement($item, $elementName, $text);
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

    protected function validateNumericElement($item, $elementName, $text)
    {
        if (!is_numeric($text))
        {
            AvantElements::addError($item, $elementName, __('Value must be numeric.'));
        }
    }
}
