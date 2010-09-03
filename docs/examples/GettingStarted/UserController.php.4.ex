function createAction(){
    $user = new User();
    $view = $this->getView();
    $view->assign('user', $user);
    $view->display('user/create.tpl');
}