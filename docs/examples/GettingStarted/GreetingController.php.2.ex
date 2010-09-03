<?php
Pfw_Loader::loadClass('Prj_Controller_Standard');
 
class GreetingController extends Prj_Controller_Standard {
    public function __construct() {
        parent::__construct();
        // do any view initialization in here
        $this->getView()->assign('all_action_var', 'this will be set');
    }
 
    function indexAction() {
        $view = $this->getView();
        $view->display(array('layout' => 'layouts/main.tpl', 'body' => 'greeting/index.tpl'));
     }
 
    function helloAction() {
        $id = $this->getParam('id');
        $mood = $this->getParam('mood');
 
        $view = $this->getView();
        $view->assign('id', $id);
        $view->assign('mood', $mood);
        $view->display(array('layout' => 'layouts/main.tpl', 'body' => 'greeting/hello.tpl'));
    }
}
