<?php
class CustomCallback
{
    const CALLBACK_ACTION_DEFAULT = 'default';
    const CALLBACK_ACTION_FILTER = 'filter';
    const CALLBACK_ACTION_SAVE = 'save';
    const CALLBACK_ACTION_SUGGEST = 'suggest';
    const CALLBACK_ACTION_VALIDATE = 'validate';

    protected $callbackElements;

    public function __construct()
    {
        $this->callbackElements = ElementsConfig::getOptionDataForCallback();
    }

    protected function callUserFunction($callbackFunctionName, $item, $elementId = 0, $text = '')
    {
        return call_user_func($callbackFunctionName, $item, $elementId, $text);
    }

    protected function formCallbackFunctionName($definition, $item, $elementId)
    {
        $callbackFunctionName = $definition['class'] . '::' . $definition['function'];

        if (!is_callable($callbackFunctionName))
        {
            if ($item)
            {
                $target = $elementId == 0 ? '<item>' : ItemMetadata::getElementNameFromId($elementId);
                AvantElements::addError($item, $target, __('Callback %s function \'%s\' is not callable.', $definition['action'], $callbackFunctionName));
            }
            $callbackFunctionName = '';
        }

        return $callbackFunctionName;
    }

    protected function getCallbackFunctionName($callbackAction, $item, $elementId)
    {
        foreach ($this->callbackElements as $callbackElementId => $callbackElement)
        {
            if ($elementId != $callbackElementId)
            {
                continue;
            }
            $definitions = $callbackElement['callbacks'];
            foreach ($definitions as $definition)
            {
                if ($definition['action'] != $callbackAction)
                {
                    continue;
                }
                return $this->formCallbackFunctionName($definition, $item, $elementId);
            }
        }
        return '';
    }

    public function performCallbackForElement($callbackAction, $item, $elementId, $text = '')
    {
        $result = null;
        $callbackFunctionName = $this->getCallbackFunctionName($callbackAction, $item, $elementId);
        if (!empty($callbackFunctionName))
        {
            $result = $this->callUserFunction($callbackFunctionName, $item, $elementId, $text);
        }
        return $result;
    }

    public function performCallbackForItem($callbackAction, $item)
    {
        // Indicate tht this callback is for an item, not a specific element.
        $itemSpecifier = 0;
        return $this->performCallbackForElement($callbackAction, $item, $itemSpecifier);
    }
}