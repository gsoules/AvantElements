<?php
class LinkBuilder
{
    protected $externalLinkDefinitions = array();

    public function __construct(&$filters)
    {
        $this->initializeImplicitLinkFilters($filters);
        $this->initializeExternalLinkFilters($filters);
    }

    public function buildLink($filterName, $elementId, $text)
    {
        $elementName = ItemMetadata::getElementNameFromId($elementId);

        if (strpos($filterName, 'filterLinkImplicit') === 0)
        {
            $text = $this->filterImplicitLink($text, $elementId);
        }
        else if (strpos($filterName, 'filterLinkExternal') === 0)
        {
            $text = $this->filterExternalLink($text, $elementName);
        }

        return $text;
    }

    protected function emitAdvancedSearchLink($elementName, $text, $secondLink = '')
    {
        $elementId = ItemMetadata::getElementIdForElementName($elementName);
        $url = ItemSearch::getAdvancedSearchUrl($elementId, $text);
        return "<div class='element-text'><p>$secondLink<a href='$url' class='metadata-search-link' title='See other items where $elementName is \"$text\"'>$text </a></p></div>";
    }

    public function emitExternalLink($href, $linkText, $openInNewTab, $class)
    {
        if (empty($class))
            $class = 'metadata-external-link';
        $html = "<a href='$href' class='$class'";

        if ($openInNewTab)
            $html .= " target='_blank'";

        if (empty($linkText))
            $linkText = $href;

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

    protected function filterExternalLink($href, $elementName)
    {
        $definition = $this->externalLinkDefinitions[$elementName];
        $class = $definition['class'];
        $openInNewTab = $definition['open-in-new-tab'] == 'true';
        $linkText = $definition['link-text'];

        return $this->emitExternalLink($href, $linkText, $openInNewTab, $class);
    }

    protected function filterImplicitLink($text, $elementId)
    {
        return $this->emitImplicitLink($elementId, $text);
    }

    public function initializeImplicitLinkFilters(&$filters)
    {
        $elementsData = ElementsConfig::getOptionDataForImplicitLink();
        foreach ($elementsData as $elementName)
        {
            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                // Set up a call to be made when this element is displayed on a Show page.
                $filters['filterLinkImplicit' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }

    public function initializeExternalLinkFilters(&$filters)
    {
        $linksData = ElementsConfig::getOptionDataForExternalLink();

        foreach ($linksData as $elementId => $link)
        {
            $elementName = $link['name'];
            $this->externalLinkDefinitions[$elementName]['open-in-new-tab'] = $link['action'];
            $this->externalLinkDefinitions[$elementName]['link-text'] = $link['text'];
            $this->externalLinkDefinitions[$elementName]['class'] = $link['class'];

            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                // Set up a call to be made when this element is displayed on a Show page.
                $filters['filterLinkExternal' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }
}