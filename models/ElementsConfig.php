<?php
class ElementsConfig extends CommonConfig
{
    const OPTION_ADD_INPUT = 'avantelements_allow_add_input';
    const OPTION_CALLBACK = 'avantelements_callback';
    const OPTION_DISPLAY_ORDER = 'avantelements_display_order';
    const OPTION_EXTERNAL_LINK = 'avantelements_external_link';
    const OPTION_HTML = 'avantelements_allow_html';
    const OPTION_IMPLICIT_LINK = 'avantelements_implicit_link';
    const OPTION_TEXT_FIELD = 'avantelements_text_field';
    const OPTION_VALIDATION = 'avantelements_validation';

    public static function getOptionDataForAddInput()
    {
        return self::getOptionData(self::OPTION_ADD_INPUT);
    }

    public static function getOptionDataForDisplayOrder()
    {
        return self::getOptionData(self::OPTION_DISPLAY_ORDER);
    }

    public static function getOptionDataForCallback()
    {
        $rawData = json_decode(get_option(self::OPTION_CALLBACK), true);
        if (empty($rawData))
        {
            $rawData = array();
        }

        $data = array();

        foreach ($rawData as $elementId => $callbackDefinition)
        {
            if ($elementId == 0)
            {
                $elementName = '<item>';
            }
            else
            {
                $elementName = ItemMetadata::getElementNameFromId($elementId);
                if (empty($elementName))
                {
                    // This element must have been deleted since the AvantElements configuration was last saved.
                    continue;
                }
            }

            $data[$elementId]['name'] = $elementName;
            $data[$elementId]['callbacks'] = $callbackDefinition;
        }

        return $data;
    }

    public static function getOptionDataForExternalLink()
    {
        $rawData = json_decode(get_option(self::OPTION_EXTERNAL_LINK), true);
        if (empty($rawData))
        {
            $rawData = array();
        }

        $data = array();

        foreach ($rawData as $elementId => $linkData)
        {
            $elementName = ItemMetadata::getElementNameFromId($elementId);
            if (empty($elementName))
            {
                // This element must have been deleted since the AvantElements configuration was last saved.
                continue;
            }
            $linkData['name'] = $elementName;
            $data[$elementId] = $linkData;
        }

        return $data;
    }

    public static function getOptionDataForHtml()
    {
        return self::getOptionData(self::OPTION_HTML);
    }

    public static function getOptionDataForImplicitLink()
    {
        return self::getOptionData(self::OPTION_IMPLICIT_LINK);
    }

    public static function getOptionDataForTextField()
    {
        $rawData = json_decode(get_option(self::OPTION_TEXT_FIELD), true);
        if (empty($rawData))
        {
            $rawData = array();
        }

        $data = array();

        foreach ($rawData as $elementId => $textFieldData)
        {
            $elementName = ItemMetadata::getElementNameFromId($elementId);
            if (empty($elementName))
            {
                // This element must have been deleted since the AvantElements configuration was last saved.
                continue;
            }
            $textFieldData['name'] = $elementName;
            $data[$elementId] = $textFieldData;
        }

        return $data;
    }

    public static function getOptionDataForValidation()
    {
        $rawData = json_decode(get_option(self::OPTION_VALIDATION), true);
        if (empty($rawData))
        {
            $rawData = array();
        }

        $data = array();

        foreach ($rawData as $elementId => $validationData)
        {
            $elementName = ItemMetadata::getElementNameFromId($elementId);
            if (empty($elementName))
            {
                // This element must have been deleted since the AvantElements configuration was last saved.
                continue;
            }
            $validationData['name'] = $elementName;
            $data[$elementId] = $validationData;
        }

        return $data;
    }

    public static function getOptionTextForAddInput()
    {
        return self::getOptionText(self::OPTION_ADD_INPUT);
    }

    public static function getOptionTextForCallback()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_CALLBACK];
        }
        else
        {
            $data = self::getOptionDataForCallback();
            $text = '';

            foreach ($data as $elementId => $callbackDefinition)
            {
                $elementName = $callbackDefinition['name'];

                foreach ($callbackDefinition['callbacks'] as $callback)
                {
                    $callbackType = $callback['type'];
                    $className = $callback['class'];
                    $functionName = $callback['function'];
                    if (!empty($text))
                    {
                        $text .= PHP_EOL;
                    }
                    $text .= "$elementName, $callbackType: $className, $functionName";
                }
            }
        }
        return $text;
    }

    public static function getOptionTextForDisplayOrder()
    {
        return self::getOptionText(self::OPTION_DISPLAY_ORDER);
    }

    public static function getOptionTextForExternalLink()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_EXTERNAL_LINK];
        }
        else
        {
            $data = self::getOptionDataForExternalLink();
            $text = '';

            foreach ($data as $elementId => $link)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $name = $link['name'];
                $text .= $name;
                if (!empty($link['text']))
                    $text .= ', ' . $link['text'];
                $text .= ': ';
                $text .= $link['action'] . ', ';
                if (!empty($link['class']))
                    $text .= $link['class'] . ', ';

                // Remove the trailing comma.
                $text = substr($text, 0, strlen($text) - 2);
            }
        }
        return $text;
    }

    public static function getOptionTextForHtml()
    {
        return self::getOptionText(self::OPTION_HTML);
    }

    public static function getOptionTextForImplicitLink()
    {
        return self::getOptionText(self::OPTION_IMPLICIT_LINK);
    }

    public static function getOptionTextForTextField()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_TEXT_FIELD];
        }
        else
        {
            $data = self::getOptionDataForTextField();
            $text = '';

            foreach ($data as $elementId => $textField)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $name = $textField['name'];
                $text .= $name;
                $width = $textField['width'];
                if ($width > 0)
                {
                    $text .= ': ' . $width;
                }
            }
        }
        return $text;
    }

    public static function getOptionTextForValidation()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_VALIDATION];
        }
        else
        {
            $data = self::getOptionDataForValidation();
            $text = '';

            foreach ($data as $elementId => $validation)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $args = $validation['args'];

                $name = $validation['name'];
                $text .= $name . ': ';

                foreach ($args as $argName => $arg)
                {
                    if ($arg == true)
                    {
                        $text .= $argName . ', ';
                    }
                }

                // Remove the trailing comma.
                $text = substr($text, 0, strlen($text) - 2);
            }
        }
        return $text;
    }

    public static function saveConfiguration()
    {
        self::saveOptionDataForDisplayOrder();
        self::saveOptionDataForExternalLink();
        self::saveOptionDataForImplicitLink();
        self::saveOptionDataForValidation();
        self::saveOptionDataForAddInput();
        self::saveOptionDataForHtml();
        self::saveOptionDataForTextField();
        self::saveOptionDataForCallback();
    }

    public static function saveOptionDataForDisplayOrder()
    {
        self::saveOptionData(self::OPTION_DISPLAY_ORDER, __('Display Order'));
    }

    public static function saveOptionDataForAddInput()
    {
        self::saveOptionData(self::OPTION_ADD_INPUT, __('Allow Add Input'));
    }

    public static function saveOptionDataForCallback()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_CALLBACK]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Callback definitions are of the form: <element-name> "," <callback-type> ":" <function-name>
            $parts = array_map('trim', explode(':', $definition));

            $nameParts = array_map('trim', explode(',', $parts[0]));

            $elementName = $nameParts[0];

            $isItemCallback = $elementName == '<item>';

            if ($isItemCallback)
            {
                $elementId = 0;
            }
            else
            {
                $elementId = ItemMetadata::getElementIdForElementName($elementName);
                if ($elementId == 0)
                {
                    throw new Omeka_Validate_Exception(__('Callback: \'%s\' is not an element.', $elementName));
                }
            }

            $callbackType = isset($nameParts[1]) ? $nameParts[1] : '';
            $types = $isItemCallback ? array ('validate') : array('filter', 'validate', 'default');

            if (empty($callbackType) || !in_array($callbackType, $types))
            {
                throw new Omeka_Validate_Exception(__('Callback (%s): Missing or invalid callback type.', $elementName));
            }

            if (!isset($parts[1]))
            {
                throw new Omeka_Validate_Exception(__('Callback Filter (%s): No callback function specified.', $elementName));
            }

            $functionParts = array_map('trim', explode(',', $parts[1]));
            $className = $functionParts[0];

            if (empty($className))
            {
                throw new Omeka_Validate_Exception(__('Callback Filter (%s): No callback class specified.', $elementName));
            }

            $functionName = isset($functionParts[1]) ? $functionParts[1] : '';
            if (empty($functionName))
            {
                throw new Omeka_Validate_Exception(__('Callback Filter (%s): No callback function specified.', $elementName));
            }

            $function = "$className::$functionName";
            if (!is_callable($function))
            {
                throw new Omeka_Validate_Exception(__('Callback Filter (%s, %s): Function \'%s\' not found or is not public.', $elementName, $callbackType, $functionName));
            }

            $data[$elementId][] = array('type' => $callbackType, 'class' => $className, 'function' => $functionName);
        }

        set_option(self::OPTION_CALLBACK, json_encode($data));
    }

    public static function saveOptionDataForExternalLink()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_EXTERNAL_LINK]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Link definitions are of the form: <element-name> [ "," <link-text>] ":" <open-in-new-tab> ["," <class>]

            $parts = array_map('trim', explode(':', $definition));

            $nameParts = array_map('trim', explode(',', $parts[0]));

            $elementName = $nameParts[0];

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            if ($elementId == 0)
            {
                throw new Omeka_Validate_Exception(__('External Link: \'%s\' is not an element.', $elementName));
            }

            $linkText = isset($nameParts[1]) ? $nameParts[1] : '';

            if (!isset($parts[1]))
            {
                throw new Omeka_Validate_Exception(__('External Link (%s): At least one parameter is required.', $elementName));
            }

            $argParts = array_map('trim', explode(',', $parts[1]));

            $openInNewTab = $argParts[0];
            if (!($openInNewTab == 'true' || $openInNewTab == 'false'))
            {
                throw new Omeka_Validate_Exception(__('External Link (%s): \'%s\' is not valid for the Open action. Use \'true\' or \'false\'.', $elementName, $openInNewTab));
            }

            $class = isset($argParts[1]) ? $argParts[1] : '';

            $data[$elementId] = array('text' => $linkText, 'action' => $openInNewTab, 'class' => $class);
        }

        set_option(self::OPTION_EXTERNAL_LINK, json_encode($data));
    }

    public static function saveOptionDataForHtml()
    {
        self::saveOptionData(self::OPTION_HTML, __('Allow HTML'));
    }

    public static function saveOptionDataForImplicitLink()
    {
        self::saveOptionData(self::OPTION_IMPLICIT_LINK, __('Implicit Link'));
    }

    public static function saveOptionDataForTextField()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_TEXT_FIELD]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Text Field definitions are of the form: <element-name> ":" <width>
            $parts = array_map('trim', explode(':', $definition));

            $name = $parts[0];
            $width = isset($parts[1]) ? intval($parts[1]) : 0;

            $elementId = ItemMetadata::getElementIdForElementName($name);
            if ($elementId == 0)
            {
                throw new Omeka_Validate_Exception(__('Text Field: \'%s\' is not an element.', $name));
            }

            $data[$elementId] = array('width' => $width);
        }

        set_option(self::OPTION_TEXT_FIELD, json_encode($data));
    }

    public static function saveOptionDataForValidation()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_VALIDATION]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Validation definitions are of the form: <element-name> ":" <arg> {"," <arg>}

            $parts = array_map('trim', explode(':', $definition));

            $elementName = $parts[0];

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            if ($elementId == 0)
            {
                throw new Omeka_Validate_Exception(__('Validation: \'%s\' is not an element.', $elementName));
            }

            if (!isset($parts[1]))
            {
                throw new Omeka_Validate_Exception(__('Validation (%s): At least one validation parameter is required.', $elementName));
            }

            $argParts = array_map('trim', explode(',', $parts[1]));

            $args = array(
                'required' => false,
                'unique' => false,
                'date' => false,
                'year' => false,
                'restricted' => false,
                'readonly' => false,
            );

            // Determine which args are specified, and issue an error for any that are unrecognized.
            foreach ($argParts as $argName)
            {
                if (array_key_exists($argName, $args))
                {
                    $args[$argName] = true;
                }
                else
                {
                    throw new Omeka_Validate_Exception(__('Validation (%s): \'%s\' is not a valid parameter.', $elementName, $argName));
                }
            }

            // Remove and unspecified args so that they don't get saved.
            foreach ($args as $key => $arg)
            {
                if ($arg == false)
                {
                    unset($args[$key]);
                }
            }

            $data[$elementId] = array('args' => $args);
        }

        set_option(self::OPTION_VALIDATION, json_encode($data));
    }
}