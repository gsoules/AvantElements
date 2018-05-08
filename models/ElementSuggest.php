<?php

class ElementSuggest
{
    const MAX_SUGGESTIONS = 25;

    public static function getIdsForSuggestElements()
    {
        $elementIds = array();

        // Get Suggest elements that have custom callbacks.
        $definitions = ElementsConfig::getOptionDataForCallback();
        foreach ($definitions as $elementId => $definition)
        {
            foreach ($definition['callbacks'] as $callback)
            {
                if ($callback['action'] == CustomCallback::CALLBACK_ACTION_SUGGEST)
                {
                    $elementIds[$elementId] = $elementId;
                }
            }
        }

        // Get basic Suggest elements. If an element has been configured for both custom and basic
        // Suggest it will only appear once in the $elementIds list.
        $definitions = ElementsConfig::getOptionDataForSuggest();
        foreach ($definitions as $elementId => $definition)
        {
            $elementIds[$elementId] = $elementId;
        }

        return implode(',', $elementIds);
    }

    protected function getQueryForLike($text)
    {
        $words = explode(' ', $text);
        $words = array_map('trim', $words);
        $query = '';
        foreach ($words as $word)
        {
            if (empty($word))
            {
                continue;
            }
            if (!empty($query))
            {
                $query .= ' AND ';
            }
            $query .= "text LIKE '%$word%'";
        }
        return $query;
    }

    public function getSuggestions($elementId)
    {
        $term = $_GET['term'];

        $suggestions = '';
        $suggestElements = ElementsConfig::getOptionDataForSuggest();
        $elementHasSuggest = false;

        foreach ($suggestElements as $suggestElementId => $suggestElement)
        {
            if ($elementId == $suggestElementId)
            {
                $suggestions = $this->suggestElementValues($elementId, $term);
                $elementHasSuggest = true;
                break;
            }
        }

        if (!$elementHasSuggest)
        {
            // The elementId was not configured for Suggest. Look for a custom callback with a Suggest action. If the
            // admin configured the element for Suggest and also defined a Suggest callback, the callback gets ignored.
            $customCallback = new CustomCallback();
            $suggestions = $customCallback->performCallbackForElement(CustomCallback::CALLBACK_ACTION_SUGGEST, null, $elementId, trim($term));
        }

        if (empty($suggestions))
        {
            $suggestions = array("No suggestions for '$term'");
        }

        return json_encode($suggestions);
    }

    protected function prepareSuggestions($suggestions)
    {
        if (count($suggestions) >= self::MAX_SUGGESTIONS)
        {
            $suggestions[] = __('[ Type more letters to refine your search ]');
        }
        return $suggestions;
    }

    public function suggestElementValues($elementId, $text)
    {
        $vocabulary = AvantElements::getSimpleVocabTerms($elementId);
        if (!empty($vocabulary))
        {
            $suggestions = $vocabulary;
        }
        else
        {
            $query = $this->getQueryForLike($text);

            $db = get_db();
            $select = $db->select()
                ->from($db->ElementText, array('DISTINCT(text)'))
                ->where('element_id = ?', $elementId)
                ->where($query)
                ->limit(self::MAX_SUGGESTIONS)
                ->order('text');

            $results = $db->getTable('ElementText')->fetchObjects($select);

            $suggestions = array();
            foreach ($results as $result)
            {
                $suggestions[] = $result->text;
            }
            $suggestions = $this->prepareSuggestions($suggestions);
        }

        return $suggestions;
    }
}