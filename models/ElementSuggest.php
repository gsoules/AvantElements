<?php

class ElementSuggest
{
    public function getSuggestions()
    {
        $term = $_GET['term'];
        $suggestions = $this->filterSuggestImplicitRelationship($term);
        if (empty($suggestions))
        {
            $suggestions = array("No suggestions for '$term'");
        }
        return json_encode($suggestions);
    }

    public function filterSuggestImplicitRelationship($term)
    {
        // The term 'implicit relationship' means a relationship that is not established using the Relationship
        // mechanism that this plugin provides, but that is implied. As an example, if item X is the biography for
        // a person, and item Y is a photograph and its Creator element is the title of item X, then items X and Y
        // have an implicit creator/creation relationship.

        // Find titles that contain all of the keywords.
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
            $query .= "+$word";
        }

        $maxResults = 250;

        $db = get_db();
        $select = $db->select()
            ->from($db->SearchTexts)
            ->where("MATCH (title) AGAINST ('$query' IN BOOLEAN MODE)")
            ->order('title');

        $results = $db->getTable('ElementText')->fetchObjects($select);

        // Add titles to a suggestions list, but only if the title's item is of type Article.
        $suggestions = array();
        $count = 0;
        foreach ($results as $result)
        {
            if ($result->record_type != 'Item')
                continue;

            $item = get_record_by_id('Item', $result->record_id);
            $type = metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));
            if (empty($type))
                continue;
            if (strpos($type, 'Article') === 0)
            {
                $suggestions[] = $result->title;
                $count++;
                if ($count >= $maxResults)
                {
                    $suggestions[] = __('STOPPED SEARCHING AFTER %s RESULTS', $count);
                    break;
                }
            }
        }

        return $suggestions;
    }
}