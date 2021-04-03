<?php
class LinkBuilder
{
    protected $externalLinkDefinitions = array();

    public function buildLink($filterName, $elementId, $text)
    {
        if (strpos($filterName, 'filterLinkImplicit') === 0)
        {
            $text = $this->filterImplicitLink($text, $elementId);
        }
        else if (strpos($filterName, 'filterLinkExternal') === 0)
        {
            $elementName = ItemMetadata::getElementNameFromId($elementId);
            $text = $this->filterExternalLink($text, $elementName);
        }

        return $text;
    }

    public function emitExternalLink($text, $defaultLinkText, $openInNewTab)
    {
        $class = 'metadata-external-link';

        $parts = array_map('trim', explode(PHP_EOL, $text));
        if (count($parts) == 1)
        {
            $href = $parts[0];
            $linkText = empty($defaultLinkText) ? $href : $defaultLinkText;
        }
        else
        {
            $href = $parts[1];
            $linkText = $parts[0];
        }

        $prefix = strtolower($href);
        if (!(substr($prefix, 0, 7) == 'http://' || substr($prefix, 0, 8) == 'https://'))
        {
            $href = 'http://' . $href;
        }

        $html = "<a href='$href' class='$class'";

        if ($openInNewTab)
            $html .= " target='_blank'";

        $html .= ">$linkText</a>";

        return $html;
    }

    public function emitImplicitLink($elementId, $text)
    {
        $results = ItemMetadata::getItemsWithElementValue($elementId, $text);

        if (count($results) < 2)
        {
            // Don't emit a link if no other items have this element's value.
            return $text;
        }

        $url = ItemSearch::getAdvancedSearchUrl($elementId, $text);
        $title = __('See other items that have this value');
        return "<a href='$url' class='metadata-search-link' title='$title'>$text</a>";
    }

    protected function filterExternalLink($text, $elementName)
    {
        $definition = $this->externalLinkDefinitions[$elementName];
        $openInNewTab = $definition['open-in-new-tab'] == 'true';
        $linkText = $definition['link-text'];

        return $this->emitExternalLink($text, $linkText, $openInNewTab);
    }

    protected function filterImplicitLink($text, $elementId)
    {
        return $this->emitImplicitLink($elementId, $text);
    }

    protected function initializeExternalLinkFilters(&$filters)
    {
        $linksData = ElementsConfig::getOptionDataForExternalLink();

        foreach ($linksData as $elementId => $link)
        {
            $elementName = $link['name'];
            $this->externalLinkDefinitions[$elementName]['open-in-new-tab'] = $link['action'];
            $this->externalLinkDefinitions[$elementName]['link-text'] = $link['text'];

            $elementSetName = ItemMetadata::getElementSetNameForElementName($elementName);
            if (!empty($elementSetName))
            {
                // Set up a call to be made when this element is displayed on a Show page.
                $filters['filterLinkExternal' . $elementName] = array('Display', 'Item', $elementSetName, $elementName);
            }
        }
    }

    public function initializeFilters(&$filters)
    {
        $this->initializeImplicitLinkFilters($filters);
        $this->initializeExternalLinkFilters($filters);
    }

    protected function initializeImplicitLinkFilters(&$filters)
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
}
