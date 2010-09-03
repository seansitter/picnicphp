<?php
// app/controllers/GreetingController.php
Pfw_Loader::loadClass('Prj_Controller_Standard');

class GreetingController extends Prj_Controller_Standard {
    public function __construct() {
        parent::__construct();
            // do any view initialization in here
        }

        function indexAction() {
            $view = $this->getView();
            $view->display(array('layout' => 'layouts/main.tpl', 'body' => 'greeting/index.tpl'));
        }
}