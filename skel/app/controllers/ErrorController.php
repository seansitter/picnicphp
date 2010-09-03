<?php

Pfw_Loader::loadClass('Prj_Controller_Standard');

class ErrorController extends Prj_Controller_Standard
{
    function fourohfourAction()
    {
        header("HTTP/1.0 404 Not Found", true, 404);
        $view = $this->getView();
        $view->display(array('layout' => 'layouts/main.tpl', 'body' => 'error/404.tpl'));
    }
}
