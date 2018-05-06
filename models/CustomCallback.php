<?php
class CustomCallback
{
    const CALLBACK_ACTION_DEFAULT = 'default';
    const CALLBACK_ACTION_FILTER = 'filter';
    const CALLBACK_ACTION_SAVE = 'save';
    const CALLBACK_ACTION_VALIDATE = 'validate';

    protected $callbacks;

    public function __construct()
    {
        $this->callbacks = ElementsConfig::getOptionDataForCallback();
    }

    protected function callUserFunction($callbackFunctionName, $item, $elementId = 0, $text = '')
    {
        return call_user_func($callbackFunctionName, $item, $elementId, $text);
    }

    protected function constructCallbackFunctionName($callbackDefinition, $callbackAction, Item $item, $elementId)
    {
        if ($callbackDefinition['action'] != $callbackAction)
        {
            return '';
        }

        $callbackFunctionName = $callbackDefinition['class'] . '::' . $callbackDefinition['function'];

        if (!is_callable($callbackFunctionName))
        {
            $target = $elementId == 0 ? '<item>' : ItemMetadata::getElementNameFromId($elementId);
            AvantElements::addError($item, $target, __('Callback %s function \'%s\' is not callable.', $callbackDefinition['action'], $callbackFunctionName));
            $callbackFunctionName = '';
        }

        return $callbackFunctionName;
    }

    protected function getCallbackFunctionName($callbackAction, $item, $elementId)
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
                return $this->constructCallbackFunctionName($callback, $callbackAction, $item, $elementId);
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