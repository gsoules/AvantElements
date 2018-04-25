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
        $elementsData = ElementsOptions::getOptionDataForImplicitLink();
        foreach ($elementsData as $elementName)
        {
            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                $filters['filterLinkImplicit' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }

    public function initializeExternalLinkFilters(&$filters)
    {
        $linksData = ElementsOptions::getOptionDataForExternalLink();

        foreach ($linksData as $elementId => $link)
        {
            $elementName = $link['name'];
            $this->externalLinkDefinitions[$elementName]['open-in-new-tab'] = $link['action'];
            $this->externalLinkDefinitions[$elementName]['link-text'] = $link['text'];
            $this->externalLinkDefinitions[$elementName]['class'] = $link['class'];

            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                $filters['filterLinkExternal' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }
}