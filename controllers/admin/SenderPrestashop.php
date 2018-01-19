<?php

require_once(dirname(__FILE__) . '/../../lib/Sender/SenderApiClient.php');

/**
* Admin View Controller
*
*
*/
class SenderPrestashopController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
    }

    public function renderList()
    {
        $api = new SenderApiClient();
        $api->setApiKey('labas');

        $lists = $api->getAllLists();

        $listHtml = '';
        
        foreach ($lists as $list) {
            $listHtml .= '</li>' . $list->title . '</li><br>';
        }

        $header  = '<div class="alert alert-info">
        				<h1>Go and die;</h1> <br>
        				<div class="well"><ul>'.$listHtml.'</ul></div>
        			</div>';
        
        return $header . parent::renderList();
    }
}
