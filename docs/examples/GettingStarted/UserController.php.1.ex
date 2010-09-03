<?php
// app/controllers/UserController.php
class UserController extends Prj_Controller_Standard {
    function __construct(){
        parent::__construct();
        $this->getView()->setDefaultLayout('layouts/main.tpl');
    }
}