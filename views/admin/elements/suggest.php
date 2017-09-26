<?php
$action = isset($_POST['action']) ? $_POST['action'] : 0;
if ($action == 0)
    return;

$elementSuggest = new ElementSuggest();
echo $elementSuggest->performAction($action);
