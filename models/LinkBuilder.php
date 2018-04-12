<?php
class LinkBuilder
{
    protected $externalLinkDefinitions = array();

    public function __construct(&$filters)
    {
        $this->initializeImplicitLinkFilters($filters);
        $this->initializeExternalLinkFilters($filters);
    }

    public function buildLink($name, $arguments)
    {
        $text = '';

        if (strpos($name, 'filterLinkImplicit') === 0)
        {
            $text = $this->filterImplicitLink($arguments);
        }
        else if (strpos($name, 'filterLinkExternal') === 0)
        {
            $text = $this->filterExternalLink($arguments);
        }

        return $text;
    }

    protected function emitAdvancedSearchLink($elementName, $text, $secondLink = '')
    {
        $elementId = ItemMetadata::getElementIdForElementName($elementName);
        $url = ItemSearch::getAdvancedSearchUrl($elementId, $text);
        return "<div class='element-text'><p>$secondLink<a href='$url' class='metadata-search-link' title='See other items where $elementName is \"$text\"'>$text </a></p></div>";
    }

    protected function emitExternalLink($text, $definition)
    {
        $class = $definition['class'];
        if (empty($class))
            $class = 'metadata-external-link';
        $html = "<a href='$text' class='$class'";

        if ($definition['open-in-new-tab'] == 'true')
            $html .= " target='_blank'";

        $linkText = $definition['link-text'];
        if (empty($linkText))
            $linkText = $text;

        $html .= ">$linkText</a>";
        return $html;
    }

    protected function emitImplicitLink($elementId, $text)
    {
        $results = ItemMetadata::getItemsWithElementValue($elementId, $text);

        if (count($results) < 2)
        {
            // Don't emit a link if no other items have this element's value.
            return $text;
        }

        $url = ItemSearch::getAdvancedSearchUrl($elementId, $text);
        return "<div class='element-text'><p><a href='$url' class='metadata-search-link' title='See other items that have this value'>$text</a></p></div>";
    }

    protected function filterExternalLink($arguments)
    {
        $text = $arguments[0];
        $elementId = $arguments[1]['element_text']['element_id'];
        $elementName = ItemMetadata::getElementNameFromId($elementId);
        $definition = $this->externalLinkDefinitions[$elementName];
        return $this->emitExternalLink($text, $definition);
    }

    protected function filterImplicitLink($arguments)
    {
        $text = $arguments[0];
        $elementId = $arguments[1]['element_text']['element_id'];
        return $this->emitImplicitLink($elementId, $text);
    }

    public function initializeImplicitLinkFilters(&$filters)
    {
        $elementNames = explode(',', get_option('avantelements_implicit_link'));
        $elementNames = array_map('trim', $elementNames);

        foreach ($elementNames as $elementName)
        {
            if (empty($elementName))
            {
                continue;
            }
            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                $filters['filterLinkImplicit' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }

    public function initializeExternalLinkFilters(&$filters)
    {
        $linkDefinitions = explode(';', get_option('avantelements_external_link'));
        $linkDefinitions = array_map('trim', $linkDefinitions);

        foreach ($linkDefinitions as $linkDefinition)
        {
            if (empty($linkDefinition))
            {
                continue;
            }

            $parts = explode(',', $linkDefinition);
            $parts = array_map('trim', $parts);
            $elementName = $parts[0];
            $openAction = isset($parts[1]) ? $parts[1] : 'true';

            $this->externalLinkDefinitions[$elementName]['open-in-new-tab'] = strtolower($openAction) == 'true';
            $this->externalLinkDefinitions[$elementName]['link-text'] = isset($parts[2]) ? $parts[2] : '';
            $this->externalLinkDefinitions[$elementName]['class'] = isset($parts[3]) ? $parts[3] : '';

            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                $filters['filterLinkExternal' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }
}