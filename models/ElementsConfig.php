<?php

define('CONFIG_LABEL_ADD_INPUT', __('Allow Add Input'));
define('CONFIG_LABEL_CALLBACK', __('Custom Callback'));
define('CONFIG_LABEL_CHECKBOX_FIELD', __('Checkbox Field'));
define('CONFIG_LABEL_DEFAULT_VALUE', __('Default Value'));
define('CONFIG_LABEL_DISPLAY_ORDER', __('Display Order'));
define('CONFIG_LABEL_EXTERNAL_LINK', __('External Link'));
define('CONFIG_LABEL_EXTERNAL_LINK_ICON', __('External Link Icon'));
define('CONFIG_LABEL_HTML', __('Allow HTML'));
define('CONFIG_LABEL_IMPLICIT_LINK', __('Implicit Link'));
define('CONFIG_LABEL_PLACEHOLDER', __('Placeholder'));
define('CONFIG_LABEL_READONLY_FIELD', __('Read-only Field'));
define('CONFIG_LABEL_SELECT_FIELD', __('Vocabulary Field'));
define('CONFIG_LABEL_SHOW_COMMENT', __('Show Comment'));
define('CONFIG_LABEL_SHOW_DESCRIPTION', __('Show Description'));
define('CONFIG_LABEL_SUGGEST', __('Suggest'));
define('CONFIG_LABEL_TEXT_FIELD', __('Text Field'));
define('CONFIG_LABEL_TEXTAREA_ROWS', __('Textarea Rows'));
define('CONFIG_LABEL_TITLE_SYNC', __('Title Sync'));
define('CONFIG_LABEL_VALIDATION', __('Validation'));

class ElementsConfig extends ConfigOptions
{
    const OPTION_ADD_INPUT = 'avantelements_allow_add_input';
    const OPTION_CALLBACK = 'avantelements_callback';
    const OPTION_CHECKBOX_FIELD = 'avantelements_checkbox_field';
    const OPTION_DEFAULT_VALUE = 'avantelements_default_value';
    const OPTION_DISPLAY_ORDER = 'avantelements_display_order';
    const OPTION_EXTERNAL_LINK = 'avantelements_external_link';
    const OPTION_EXTERNAL_LINK_ICON = 'avantelements_external_link_icon';
    const OPTION_HTML = 'avantelements_allow_html';
    const OPTION_IMPLICIT_LINK = 'avantelements_implicit_link';
    const OPTION_PLACEHOLDER = 'avantelements_placeholder';
    const OPTION_READONLY_FIELD = 'avantelements_readonly_field';
    const OPTION_SELECT_FIELD = 'avantelements_select_field';
    const OPTION_SHOW_COMMENT = 'avantelements_show_comment';
    const OPTION_SHOW_DESCRIPTION = 'avantelements_show_description';
    const OPTION_SUGGEST = 'avantelements_suggest';
    const OPTION_TEXT_FIELD = 'avantelements_text_field';
    const OPTION_TEXTAREA_ROWS = 'avantelements_textarea_rows';
    const OPTION_TITLE_SYNC = 'avantelements_title_sync';
    const OPTION_VALIDATION = 'avantelements_validation';
	const OPTION_ACCEPT_ALLELEMENTS_VALUE = true;

    public static function getOptionDataForAddInput()
    {
        return self::getOptionListData(self::OPTION_ADD_INPUT, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function getOptionDataForCheckboxField()
    {
        return self::getOptionDefinitionData(self::OPTION_CHECKBOX_FIELD);
    }

    public static function getOptionDataForDefaultValue()
    {
        return self::getOptionDefinitionData(self::OPTION_DEFAULT_VALUE);
    }

    public static function getOptionDataForDisplayOrder()
    {
        return self::getOptionListData(self::OPTION_DISPLAY_ORDER);
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
        return self::getOptionDefinitionData(self::OPTION_EXTERNAL_LINK);
    }

    public static function getOptionDataForHideComment()
    {
        return self::getOptionListData(self::OPTION_SHOW_COMMENT, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function getOptionDataForHideDescription()
    {
        return self::getOptionListData(self::OPTION_SHOW_DESCRIPTION, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function getOptionDataForHtml()
    {
        return self::getOptionListData(self::OPTION_HTML, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function getOptionDataForImplicitLink()
    {
        return self::getOptionListData(self::OPTION_IMPLICIT_LINK);
    }

    public static function getOptionDataForPlaceholder()
    {
        return self::getOptionDefinitionData(self::OPTION_PLACEHOLDER);
    }

    public static function getOptionDataForReadonlyField()
    {
        return self::getOptionListData(self::OPTION_READONLY_FIELD, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function getOptionDataForTextField()
    {
		return self::getOptionDefinitionData(self::OPTION_TEXT_FIELD);
    }

    public static function getOptionDataForSelectField()
    {
        return self::getOptionDefinitionData(self::OPTION_SELECT_FIELD);
    }

    public static function getOptionDataForSuggest()
    {
        return self::getOptionListData(self::OPTION_SUGGEST);
    }

    public static function getOptionDataForTitleSync()
    {
        return self::getOptionListData(self::OPTION_TITLE_SYNC);
    }

    public static function getOptionDataForValidation()
    {
        return self::getOptionDefinitionData(self::OPTION_VALIDATION);
    }

    public static function getOptionTextForAddInput()
    {
        return self::getOptionListText(self::OPTION_ADD_INPUT);
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

    public static function getOptionTextForCheckboxField()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_CHECKBOX_FIELD];
        }
        else
        {
            $data = self::getOptionDataForCheckboxField();
            $text = '';

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $elementName = $definition['name'];
                $checked = $definition['checked'];
                $text = "$elementName: $checked";
            }
        }
        return $text;
    }

    public static function getOptionTextForDefaultValue()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_DEFAULT_VALUE];
        }
        else
        {
            $data = self::getOptionDataForDefaultValue();
            $text = '';

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $name = $definition['name'];
                $value = $definition['value'];
                $text .= "$name: $value";
            }
        }
        return $text;
    }

    public static function getOptionTextForDisplayOrder()
    {
        return self::getOptionListText(self::OPTION_DISPLAY_ORDER);
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

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $text .= $definition['name'];
                $text .= $definition['action'] == 'true' ? '' : ', false';
                $linkText = $definition['text'];
                $text .= strlen($linkText) > 0 ? ": $linkText" : '';
            }
        }
        return $text;
    }

    public static function getOptionTextForHideDescription()
    {
        return self::getOptionListText(self::OPTION_SHOW_DESCRIPTION);
    }

    public static function getOptionTextForHideComment()
    {
        return self::getOptionListText(self::OPTION_SHOW_COMMENT);
    }

    public static function getOptionTextForHtml()
    {
        return self::getOptionListText(self::OPTION_HTML);
    }

    public static function getOptionTextForImplicitLink()
    {
        return self::getOptionListText(self::OPTION_IMPLICIT_LINK);
    }

    public static function getOptionTextForPlaceholder()
    {
        if (self::configurationErrorsDetected())
        {
            $text = $_POST[self::OPTION_PLACEHOLDER];
        }
        else
        {
            $data = self::getOptionDataForPlaceholder();
            $text = '';

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $name = $definition['name'];
                $text .= $name;
                $placeholder = $definition['placeholder'];
                if ($placeholder != '')
                {
                    $text .= ': ' . $placeholder;
                }
            }
        }
        return $text;
    }

    public static function getOptionTextForReadonlyField()
    {
        return self::getOptionListText(self::OPTION_READONLY_FIELD);
    }

    public static function getOptionTextForSelectField()
    {
        if (self::configurationErrorsDetected())
        {
            $text = plugin_is_active('SimpleVocab') || plugin_is_active('AvantVocabulary') ? $_POST[self::OPTION_SELECT_FIELD] : '';
        }
        else
        {
            $data = self::getOptionDataForSelectField();
            $text = '';

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $name = $definition['name'];
                $text .= $name;
                $width = $definition['width'];
                if ($width > 0)
                {
                    $text .= ': ' . $width;
                }
            }
        }
        return $text;
    }

    public static function getOptionTextForSuggest()
    {
        return self::getOptionListText(self::OPTION_SUGGEST);
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

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $name = $definition['name'];
                $text .= $name;
                $width = $definition['width'];
                if ($width > 0)
                {
                    $text .= ': ' . $width;
                }
            }
        }
        return $text;
    }
	
    public static function getOptionTextForTextareaRows()
    {
		$value = get_option(self::OPTION_TEXTAREA_ROWS);
        if (!intval($value))
            $value = 2;
        return $value;
    }

    public static function getOptionTextForTitleSync()
    {
        return self::getOptionListText(self::OPTION_TITLE_SYNC);
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

            foreach ($data as $elementId => $definition)
            {
                if (!empty($text))
                {
                    $text .= PHP_EOL;
                }
                $args = $definition['args'];

                $elementName = $definition['name'];
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
        self::saveOptionDataForHideComment();
        self::saveOptionDataForHideDescription();
        self::saveOptionDataForValidation();
        self::saveOptionDataForAddInput();
        self::saveOptionDataForHtml();
        self::saveOptionDataForPlaceholder();
        self::saveOptionDataForTextField();
        self::saveOptionDataForTextareaRows();
        self::saveOptionDataForSelectField();
        self::saveOptionDataForCheckboxField();
        self::saveOptionDataForReadonlyField();
        self::saveOptionDataForDefaultValue();
        self::saveOptionDataForSuggest();
        self::saveOptionDataForTitleSync();
        self::saveOptionDataForCallback();

        set_option(self::OPTION_EXTERNAL_LINK_ICON, intval($_POST[self::OPTION_EXTERNAL_LINK_ICON]));
    }

    public static function saveOptionDataForAddInput()
    {
        self::saveOptionListData(self::OPTION_ADD_INPUT, CONFIG_LABEL_ADD_INPUT, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function saveOptionDataForCallback()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_CALLBACK]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <element-name> "," <callback-type> ":" <class-name> "," <function-name>
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
                self::errorIfNotElement($elementId, CONFIG_LABEL_CALLBACK, $elementName);
            }

            $action = isset($nameParts[1]) ? $nameParts[1] : '';
            if ($isItemCallback)
            {
                $actions = array(
                    CustomCallback::CALLBACK_ACTION_VALIDATE,
                    CustomCallback::CALLBACK_ACTION_SAVE
                );
            }
            else
            {
                $actions = array(
                    CustomCallback::CALLBACK_ACTION_FILTER,
                    CustomCallback::CALLBACK_ACTION_DEFAULT,
                    CustomCallback::CALLBACK_ACTION_SUGGEST,
                    CustomCallback::CALLBACK_ACTION_VALIDATE
                );
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

            if ($action == CustomCallback::CALLBACK_ACTION_FILTER )
            {
                $implicitLinkData = self::getOptionDataForImplicitLink();
                self::errorRowIf(array_key_exists($elementId, $implicitLinkData), CONFIG_LABEL_CALLBACK, $elementName,
                    __("A custom filter for %s cannot be used because %s is specified in the Implicit Link option. " .
                        "See the Implicit Link option documentation to learn how to resolve this conflict.", $elementName, $elementName));
                $externalLinkData = self::getOptionDataForExternalLink();
                self::errorRowIf(array_key_exists($elementId, $externalLinkData), CONFIG_LABEL_CALLBACK, $elementName,
                    __("A custom filter for %s cannot be used because %s is specified in the External Link option.", $elementName, $elementName));
            }

            $data[$elementId][] = array('action' => $action, 'class' => $className, 'function' => $functionName);
        }

        set_option(self::OPTION_CALLBACK, json_encode($data));
    }

    public static function saveOptionDataForCheckboxField()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_CHECKBOX_FIELD]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <element-name> ":" <true-value>
            $parts = array_map('trim', explode(':', $definition));

            $elementName = $parts[0];
            $trueText = isset($parts[1]) ? $parts[1] : '';

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIfNotElement($elementId, CONFIG_LABEL_CHECKBOX_FIELD, $elementName);

            self::errorRowIf(empty($trueText), CONFIG_LABEL_CHECKBOX_FIELD, $elementName, __('Missing text to display when checked'));

            $data[$elementId] = array('checked' => $trueText);
        }

        set_option(self::OPTION_CHECKBOX_FIELD, json_encode($data));
    }

    public static function saveOptionDataForDefaultValue()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_DEFAULT_VALUE]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <element-name> ":" <value>
            $parts = array_map('trim', explode(':', $definition));

            $elementName = $parts[0];

            $value = isset($parts[1]) ? $parts[1] : '';
            self::errorRowIf(strlen($value) == 0, CONFIG_LABEL_DEFAULT_VALUE, $elementName, __('No value specified.'));

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIfNotElement($elementId, CONFIG_LABEL_DEFAULT_VALUE, $elementName);

            $data[$elementId] = array('value' => $value);
        }

        set_option(self::OPTION_DEFAULT_VALUE, json_encode($data));
    }

    public static function saveOptionDataForDisplayOrder()
    {
        self::saveOptionListData(self::OPTION_DISPLAY_ORDER, CONFIG_LABEL_DISPLAY_ORDER);
    }

    public static function saveOptionDataForExternalLink()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_EXTERNAL_LINK]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <element-name> [ “,” <open-in-new-tab>] [ “:” <link-text>]

            $parts = array_map('trim', explode(':', $definition));

            $nameParts = array_map('trim', explode(',', $parts[0]));

            $elementName = $nameParts[0];

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIfNotElement($elementId, CONFIG_LABEL_EXTERNAL_LINK, $elementName);

            $openInNewTab = isset($nameParts[1]) ? $nameParts[1] : 'true';
            self::errorRowIf(!($openInNewTab == 'true' || $openInNewTab == 'false'), CONFIG_LABEL_EXTERNAL_LINK, $elementName, __("'%s' is not valid for the Open action. Options: true, false.", $openInNewTab));

            $linkText = isset($parts[1]) ? $parts[1] : '';

            $data[$elementId] = array('text' => $linkText, 'action' => $openInNewTab);
        }

        set_option(self::OPTION_EXTERNAL_LINK, json_encode($data));
    }

    public static function saveOptionDataForHideDescription()
    {
        self::saveOptionListData(self::OPTION_SHOW_DESCRIPTION, CONFIG_LABEL_SHOW_DESCRIPTION, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function saveOptionDataForHideComment()
    {
        self::saveOptionListData(self::OPTION_SHOW_COMMENT, CONFIG_LABEL_SHOW_COMMENT, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function saveOptionDataForHtml()
    {
        self::saveOptionListData(self::OPTION_HTML, CONFIG_LABEL_HTML, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function saveOptionDataForImplicitLink()
    {
        self::saveOptionListData(self::OPTION_IMPLICIT_LINK, CONFIG_LABEL_IMPLICIT_LINK);
    }

    public static function saveOptionDataForPlaceholder()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_PLACEHOLDER]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <element-name> [ ":" <placeholder> ]
            $parts = array_map('trim', explode(':', $definition));

            $elementName = $parts[0];

            if (isset($parts[1]))
            {
                $placeholder = $parts[1];
            }
            else
            {
                $placeholder = '';
            }

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIfNotElement($elementId, CONFIG_LABEL_PLACEHOLDER, $elementName);

            $data[$elementId] = array('placeholder' => $placeholder);
        }

        set_option(self::OPTION_PLACEHOLDER, json_encode($data));
    }
	
    public static function saveOptionDataForReadonlyField()
    {
        self::saveOptionListData(self::OPTION_READONLY_FIELD, CONFIG_LABEL_READONLY_FIELD, self::OPTION_ACCEPT_ALLELEMENTS_VALUE);
    }

    public static function saveOptionDataForSelectField()
    {
        $data = array();
        $selectFieldValues = plugin_is_active('SimpleVocab') || plugin_is_active('AvantVocabulary') ? $_POST[self::OPTION_SELECT_FIELD] : '';
        $definitions = array_map('trim', explode(PHP_EOL, $selectFieldValues));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <element-name> ":" <width>
            $parts = array_map('trim', explode(':', $definition));

            $elementName = $parts[0];

            if (isset($parts[1]))
            {
                $width = intval($parts[1]);
                self::errorRowIf($width < 20, CONFIG_LABEL_SELECT_FIELD, $elementName, __("Width '%s' is invalid. Specify an integer greater than 20.", $parts[1]));
            }
            else
            {
                $width = 0;
            }

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIfNotElement($elementId, CONFIG_LABEL_SELECT_FIELD, $elementName);
            self::errorIf(empty(AvantElements::getVocabularyTerms($elementId)), CONFIG_LABEL_SELECT_FIELD, __("'%s' is not a vocabulary element.", $elementName));

            $data[$elementId] = array('width' => $width);
        }

        set_option(self::OPTION_SELECT_FIELD, json_encode($data));
    }

    public static function saveOptionDataForSuggest()
    {
        self::saveOptionListData(self::OPTION_SUGGEST, CONFIG_LABEL_SUGGEST);
    }

    public static function saveOptionDataForTextField()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_TEXT_FIELD]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <element-name> [ ":" <width> ]
            $parts = array_map('trim', explode(':', $definition));

            $elementName = $parts[0];

            if (isset($parts[1]))
            {
                $width = intval($parts[1]);
                self::errorRowIf($width < 20, CONFIG_LABEL_TEXT_FIELD, $elementName, __("Width '%s' is invalid. Specify an integer greater than 20.", $parts[1]));
            }
            else
            {
                $width = 0;
            }

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIfNotElement($elementId, CONFIG_LABEL_TEXT_FIELD, $elementName);

            $data[$elementId] = array('width' => $width);
        }

        set_option(self::OPTION_TEXT_FIELD, json_encode($data));
    }

    public static function saveOptionDataForTextareaRows()
    {
        $value = self::getOptionText(self::OPTION_TEXTAREA_ROWS);
        $value = trim($value);
        self::errorIf(!intval($value), CONFIG_LABEL_TEXTAREA_ROWS, __("'%s' is not valid. A number >= 1 is required.", $value));

        self::saveOptionText(self::OPTION_TEXTAREA_ROWS, CONFIG_LABEL_TEXTAREA_ROWS);
    }

    public static function saveOptionDataForTitleSync()
    {
        self::saveOptionListData(self::OPTION_TITLE_SYNC, CONFIG_LABEL_TITLE_SYNC);
    }

    public static function saveOptionDataForValidation()
    {
        $data = array();
        $definitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_VALIDATION]));
        foreach ($definitions as $definition)
        {
            if (empty($definition))
                continue;

            // Syntax: <element-name> ":" <arg> {"," <arg>}

            $parts = array_map('trim', explode(':', $definition));

            $elementName = $parts[0];

            $elementId = ItemMetadata::getElementIdForElementName($elementName);
            self::errorIfNotElement($elementId, CONFIG_LABEL_VALIDATION, $elementName);

            self::errorRowIf(!isset($parts[1]), CONFIG_LABEL_VALIDATION, $elementName, __('At least one validation parameter is required.'));

            $argParts = array_map('trim', explode(',', $parts[1]));

            $options = array(
                ElementValidator::VALIDATION_TYPE_REQUIRED,
                ElementValidator::VALIDATION_TYPE_DATE,
                ElementValidator::VALIDATION_TYPE_YEAR,
                ElementValidator::VALIDATION_TYPE_SIMPLE_TEXT,
                ElementValidator::VALIDATION_TYPE_UPPER_CASE,
                ElementValidator::VALIDATION_TYPE_LOWER_CASE,
                ElementValidator::VALIDATION_TYPE_NUMERIC,
                ElementValidator::VALIDATION_TYPE_ACCEPT_NONE);

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
