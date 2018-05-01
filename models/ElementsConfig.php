<?php

define('CONFIG_LABEL_ADD_INPUT', __('Allow Add Input'));
define('CONFIG_LABEL_CALLBACK', __('Callback'));
define('CONFIG_LABEL_DISPLAY_ORDER', __('Display Order'));
define('CONFIG_LABEL_EXTERNAL_LINK', __('External Link'));
define('CONFIG_LABEL_HTML', __('Allow HTML'));
define('CONFIG_LABEL_IMPLICIT_LINK', __('Implicit Link'));
define('CONFIG_LABEL_TEXT_FIELD', __('Text Field'));
define('CONFIG_LABEL_TITLE_SYNC', __('Title Sync'));
define('CONFIG_LABEL_VALIDATION', __('Validation'));

class ElementsConfig extends ConfigOptions
{
    const OPTION_ADD_INPUT = 'avantelements_allow_add_input';
    const OPTION_CALLBACK = 'avantelements_callback';
    const OPTION_DISPLAY_ORDER = 'avantelements_display_order';
    const OPTION_EXTERNAL_LINK = 'avantelements_external_link';
    const OPTION_HTML = 'avantelements_allow_html';
    const OPTION_IMPLICIT_LINK = 'avantelements_implicit_link';
    const OPTION_TEXT_FIELD = 'avantelements_text_field';
    const OPTION_TITLE_SYNC = 'avantelements_title_sync';
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
        $rawData = self::getRawData(self::OPTION_CALLBACK);
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
        $rawData = self::getRawData(self::OPTION_EXTERNAL_LINK);
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
        $rawData = self::getRawData(self::OPTION_TEXT_FIELD);
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

    public static function getOptionDataForTitleSync()
    {
        return self::getOptionData(self::OPTION_TITLE_SYNC);
    }

    public static function getOptionDataForValidation()
    {
        $rawData = self::getRawData(self::OPTION_VALIDATION);
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
                    $callbackAction = $callback['action'];
                    $className = $callback['class'];
                    $functionName = $callback['function'];
                    if (!empty($text))
                    {
                        $text .= PHP_EOL;
                    }
                    $text .= "$elementName, $callbackAction: $className, $functionName";
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

    public static function getOptionTextForTitleSync()
    {
        return self::getOptionText(self::OPTION_TITLE_SYNC);
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

                $elementName = $validation['name'];
                $text .= $elementName . ': ';

                foreach ($args as $argName)
                {
                    $text .= $argName . ', ';
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
        self::saveOptionDataForTitleSync();
        self::saveOptionDataForValidation();
        self::saveOptionDataForAddInput();
        self::saveOptionDataForHtml();
        self::saveOptionDataForTextField();
        self::saveOptionDataForCallback();
    }

    public static function saveOptionDataForDisplayOrder()
    {
        self::saveOptionData(self::OPTION_DISPLAY_ORDER, CONFIG_LABEL_DISPLAY_ORDER);
    }

    public static function saveOptionDataForAddInput()
    {
        self::saveOptionData(self::OPTION_ADD_INPUT, CONFIG_LABEL_ADD_INPUT);
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
                self::errorIf($elementId == 0, CONFIG_LABEL_CALLBACK, __("'%s' is not an element.", $elementName));
            }

            $action = isset($nameParts[1]) ? $nameParts[1] : '';
            if ($isItemCallback)
            {
                $actions = array(
                    ElementValidator::CALLBACK_ACTION_VALIDATE,
                    ElementValidator::CALLBACK_ACTION_SAVE);
            }
            else
            {
                $actions = array(
                    ElementValidator::CALLBACK_ACTION_VALIDATE,
                    ElementValidator::CALLBACK_ACTION_DEFAULT);
            }
            $allowed = implode(', ', $actions);

            self::errorRowIf(empty($action), CONFIG_LABEL_CALLBACK, $elementName, __('Missing callback type. Options: %s.', $allowed));
            self::errorRowIf(!in_array($action, $actions), CONFIG_LABEL_CALLBACK, $elementName, __("Invalid callback type '%s'. Options: %s.", $action, $allowed));
            self::errorRowIf(!isset($parts[1]), CONFIG_LABEL_CALLBACK, $elementName, __('No callback function specified.'));

            $functionParts = array_map('trim', explode(',', $parts[1]));
            $className = $functionParts[0];

            self::errorRowIf(empty($className), CONFIG_LABEL_CALLBACK, $elementName, __('No callback class specified.'));

            $functionName = isset($functionParts[1]) ? $functionParts[1] : '';
            self::errorRowIf(empty($functionName), CONFIG_LABEL_CALLBACK, $elementName, __('No callback function specified.'));

            $function = "$className::$functionName";
            self::errorRowIf(!is_callable($function), CONFIG_LABEL_CALLBACK, $elementName, __("%s function '%s' does not exist or is not public.", $action, $function));

            $data[$elementId][] = array('action' => $action, 'class' => $className, 'function' => $functionName);
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
            self::errorIf($elementId == 0, CONFIG_LABEL_EXTERNAL_LINK, __("'%s' is not an element.", $elementName));

            $linkText = isset($nameParts[1]) ? $nameParts[1] : '';

            self::errorRowIf(!isset($parts[1]), CONFIG_LABEL_EXTERNAL_LINK, $elementName, __('At least one parameter is required.'));

            $argParts = array_map('trim', explode(',', $parts[1]));

            $openInNewTab = $argParts[0];
            self::errorRowIf(!($openInNewTab == 'true' || $openInNewTab == 'false'), CONFIG_LABEL_EXTERNAL_LINK, $elementName, __("'%s' is not valid for the Open action. Options: true, false.", $openInNewTab));

            $class = isset($argParts[1]) ? $argParts[1] : '';

            $data[$elementId] = array('text' => $linkText, 'action' => $openInNewTab, 'class' => $class);
        }

        set_option(self::OPTION_EXTERNAL_LINK, json_encode($data));
    }

    public static function saveOptionDataForHtml()
    {
        self::saveOptionData(self::OPTION_HTML, CONFIG_LABEL_HTML);
    }

    public static function saveOptionDataForImplicitLink()
    {
        self::saveOptionData(self::OPTION_IMPLICIT_LINK, CONFIG_LABEL_IMPLICIT_LINK);
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
            self::errorIf($elementId == 0, CONFIG_LABEL_TEXT_FIELD, __("'%s' is not an element.", $name));
            self::errorIf(!empty(ElementFields::getSimpleVocabTerms($elementId)), CONFIG_LABEL_TEXT_FIELD, __("'%s' cannot be a text field. It is a SimpleVocab element that displays as a dropdown list.", $name));

            $data[$elementId] = array('width' => $width);
        }

        set_option(self::OPTION_TEXT_FIELD, json_encode($data));
    }

    public static function saveOptionDataForTitleSync()
    {
        self::saveOptionData(self::OPTION_TITLE_SYNC, CONFIG_LABEL_TITLE_SYNC);
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
            self::errorIf($elementId == 0, CONFIG_LABEL_VALIDATION, __("'%s' is not an element.", $elementName));

            self::errorRowIf(!isset($parts[1]), CONFIG_LABEL_VALIDATION, $elementName, __('At least one validation parameter is required.'));

            $argParts = array_map('trim', explode(',', $parts[1]));

            $options = array(
                ElementValidator::VALIDATION_TYPE_REQUIRED,
                ElementValidator::VALIDATION_TYPE_DATE,
                ElementValidator::VALIDATION_TYPE_YEAR,
                ElementValidator::VALIDATION_TYPE_RESTRICTED);

            // Determine which args are specified, and issue an error for any that are unrecognized.
            $args = array();
            foreach ($argParts as $argName)
            {
                if (in_array($argName, $options))
                {
                    $args[] = $argName;
                }
                else
                {
                    $allowed = implode(', ', $options);
                    self::errorRowIf(true, CONFIG_LABEL_VALIDATION, $elementName, __("'%s' is not a valid parameter. Options: %s.", $argName, $allowed));
                }
            }

            $data[$elementId] = array('args' => $args);
        }

        set_option(self::OPTION_VALIDATION, json_encode($data));
    }
}