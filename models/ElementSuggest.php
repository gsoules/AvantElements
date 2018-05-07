<?php

class ElementSuggest
{
    public function getSuggestions($elementId)
    {
        $term = $_GET['term'];
        $customCallback = new CustomCallback();

        $suggestions = $customCallback->performCallbackForElement(CustomCallback::CALLBACK_ACTION_SUGGEST, null, $elementId, trim($term));

        if (empty($suggestions))
        {
            $suggestions = array("No suggestions for '$term'");
        }
        return json_encode($suggestions);
    }
}