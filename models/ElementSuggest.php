<?php

class ElementSuggest
{
    const SUGGEST = 1;

    public function performAction($action)
    {
        switch ($action)
        {
            case ElementSuggest::SUGGEST:
                return $this->suggestValue();

            default:
                return false;
        }
    }

    protected function suggestValue()
    {
        $term = $_GET['term'];
        $suggestions = $this->filterSuggestImplicitRelationship($term);
        return json_encode($suggestions);

        // This method provides support for a suggestion feature that can be used on the Edit page to provide
        // real time information during data entry. It lets the admin type in keywords and click a Suggest button.
        // Clicking the button triggers an AJAX call that calls this method which in turn calls the
        // suggest_implicit_relationship filter. That filter queries the database to retrieve suggestions which
        // the user can choose from to automatically fill in a field.
        //
        // The term 'implicit relationship' means a relationship that is not established using the Relationship
        // mechanism that this plugin provides, but that is implied. As an example, if item X is the biography for
        // a person, and item Y is a photograph and its Creator element is the title of item X, then items X and Y
        // have an implicit creator/creation relationship.

        $keywords = isset($_POST['keywords']) ? $_POST['keywords'] : '';
        $elementId = isset($_POST['elementId']) ? $_POST['elementId'] : '';
        $message = '';

        if (empty($keywords) || empty($elementId))
        {
            $message = __('Please specify keywords to make suggestions from');
        }
        else
        {
            $suggestions = $this->filterSuggestImplicitRelationship($keywords);

            $html = '';
            $count = count($suggestions);
            if ($count > 0)
            {
                $listSize = min($count, 25);
                $html .= "<select id='suggest-$elementId' class='suggest-list' size='$listSize'>";
                foreach ($suggestions as $suggestion)
                {
                    $html .= "<option value='$suggestion'>$suggestion</option>";
                }
                $html .= '</select>';
            }
        }

        if (empty($html) && empty($message))
        {
            $message = __('No match was found for \'%s\'', $keywords);
        }

        if (!empty($message))
        {
            $html = "<div id='suggest-$elementId' class='suggest-message'>" . $message . '</div>';
        }

        return json_encode(array('success' => true, 'html' => $html));
    }

    public function filterSuggestImplicitRelationship($keywords)
    {
        // Find titles that contain all of the keywords.
        $words = explode(' ', $keywords);
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