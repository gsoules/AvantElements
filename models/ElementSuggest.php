<?php

class ElementSuggest
{
    public function getSuggestions($elementId)
    {
        $term = $_GET['term'];
        $suggestions = $this->searchTitles($term);
        if (empty($suggestions))
        {
            $suggestions = array("No suggestions for '$term'");
        }
        return json_encode($suggestions);
    }

    public function searchTitles($term)
    {
        $words = explode(' ', $term);
        $words = array_map('trim', $words);
        $query = '';
        foreach ($words as $word)
        {
            if (empty($word) || SearchQueryBuilder::isStopWord($word))
            {
                continue;
            }
            if (!empty($query))
            {
                $query .= ' ';
            }
            $query .= "+$word*";
        }

        $maxResults = 25;

        $db = get_db();
        $select = $db->select()
            ->from($db->SearchTexts, array('title'))
            ->where("MATCH (title) AGAINST ('$query' IN BOOLEAN MODE)")
            ->limit($maxResults)
            ->order('title');

        $results = $db->getTable('ElementText')->fetchObjects($select);

        $suggestions = array();
        foreach ($results as $result)
        {
            // Account for the fact that when an item has more than one title, each title value is separated in the
            // search_texts table's title column with a special delimiter. This code returns each of the titles.
            $titles = explode('||', $result->title);
            foreach ($titles as $title)
            {
                $suggestions[] = $title;
            }
        }

        return $suggestions;
    }
}