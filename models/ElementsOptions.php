<?php
class ElementsOptions extends ConfigurationOptions
{
    const OPTION_ADD_INPUT = 'avantelements_allow_add_input';
    const OPTION_DISPLAY_ORDER = 'avantelements_display_order';
    const OPTION_EXTERNAL_LINK = 'avantelements_external_link';
    const OPTION_HTML = 'avantelements_allow_html';
    const OPTION_IMPLICIT_LINK = 'avantelements_implicit_link';
    const OPTION_WIDTH = 'avantelements_width';

    public static function getOptionDataForAddInput()
    {
        return self::getOptionData(self::OPTION_ADD_INPUT);
    }

    public static function getOptionDataForDisplayOrder()
    {
        return self::getOptionData(self::OPTION_DISPLAY_ORDER);
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

    public static function getOptionTextForDisplayOrder()
    {
        return self::getOptionText(self::OPTION_DISPLAY_ORDER);
    }

    public static function getOptionTextForAddInput()
    {
        return self::getOptionText(self::OPTION_ADD_INPUT);
    }

    public static function getOptionTextForExternalLink()
    {
        if (self::configurationErrorsDetected())
        {
            $externalLinksOption = $_POST[self::OPTION_EXTERNAL_LINK];
        }
        else
        {
            $linksData = self::getOptionDataForExternalLink();
            $externalLinksOption = '';

            foreach ($linksData as $elementId => $link)
            {
                if (!empty($externalLinksOption))
                {
                    $externalLinksOption .= PHP_EOL;
                }
                $name = $link['name'];
                $externalLinksOption .= $name;
                if (!empty($link['text']))
                    $externalLinksOption .= ': ' . $link['text'];
                $externalLinksOption .= ', ' . $link['action'];
                if (!empty($link['class']))
                    $externalLinksOption .= ', ' . $link['class'];
            }
        }
        return $externalLinksOption;
    }

    public static function getOptionTextForHtml()
    {
        return self::getOptionText(self::OPTION_HTML);
    }

    public static function getOptionTextForImplicitLink()
    {
        return self::getOptionText(self::OPTION_IMPLICIT_LINK);
    }

    public static function saveConfiguration()
    {
        self::saveOptionDataForDisplayOrder();
        self::saveOptionDataForExternalLink();
        self::saveOptionDataForImplicitLink();
        self::saveOptionDataForAddInput();
        self::saveOptionDataForHtml();

        set_option('avantelements_width_70', $_POST['avantelements_width_70']);
        set_option('avantelements_width_160', $_POST['avantelements_width_160']);
        set_option('avantelements_width_250', $_POST['avantelements_width_250']);
        set_option('avantelements_width_380', $_POST['avantelements_width_380']);
    }

    public static function saveOptionDataForDisplayOrder()
    {
        self::saveOptionData(self::OPTION_DISPLAY_ORDER, __('Display Order'));
    }

    public static function saveOptionDataForAddInput()
    {
        self::saveOptionData(self::OPTION_ADD_INPUT, __('Allow Add Input'));
    }

    public static function saveOptionDataForExternalLink()
    {
        $links = array();
        $linkDefinitions = array_map('trim', explode(PHP_EOL, $_POST[self::OPTION_EXTERNAL_LINK]));
        foreach ($linkDefinitions as $linkDefinition)
        {
            if (empty($linkDefinition))
                continue;

            // Link definitions are of the form: <element-name>:<link-text>,<open-in-new-tab>,<class>
            // The <link-text> and <class> parameters are optional.

            $parts = array_map('trim', explode(',', $linkDefinition));

            $nameParts = array_map('trim', explode(':', $parts[0]));
            $name = $nameParts[0];
            $linkText = isset($nameParts[1]) ? $nameParts[1] : '';

            $openInNewTab = isset($parts[1]) ? strtolower($parts[1]) : 'true';
            $class = isset($parts[2]) ? $parts[2] : '';

            if (!($openInNewTab == 'true' || $openInNewTab == 'false'))
            {
                throw new Omeka_Validate_Exception(__('External Link (\'%s\'): \'%s\' is not valid for the Open action. Use \'true\' or \'false\'.', $name, $openInNewTab));
            }

            $elementId = ItemMetadata::getElementIdForElementName($name);
            if ($elementId == 0)
            {
                throw new Omeka_Validate_Exception(__('External Link: \'%s\' is not an element.', $name));
            }

            $links[$elementId] = array('text' => $linkText, 'action' => $openInNewTab, 'class' => $class);
        }

        set_option(self::OPTION_EXTERNAL_LINK, json_encode($links));
    }

    public static function saveOptionDataForHtml()
    {
        self::saveOptionData(self::OPTION_HTML, __('Allow HTML'));
    }

    public static function saveOptionDataForImplicitLink()
    {
        self::saveOptionData(self::OPTION_IMPLICIT_LINK, __('Implicit Link'));
    }

}