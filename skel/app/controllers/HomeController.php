<?php

Pfw_Loader::loadClass('Prj_Controller_Standard');

class HomeController extends Prj_Controller_Standard
{
    function indexAction()
    {
        $view = $this->getView();
        $view->display(array('layout' => 'layouts/main.tpl', 'body' => 'home/index.tpl'));
    }
}