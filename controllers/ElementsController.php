<?php

class AvantElements_ElementsController extends Omeka_Controller_AbstractActionController
{
    public function suggestAction()
    {
        // The element Id is passed as the last part of the suggest URL e.g. elements/suggest/50.
        $elementId = $this->getParam('element-id');
        $this->view->elementId = $elementId;
    }
}
