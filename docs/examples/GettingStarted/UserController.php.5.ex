function createAction(){
    $user = new User();

    if ($this->isPost()) {
        $user->formSetProperties($this->getParam('user'));
        if ($user->save()) {
            $this->redirectTo(array(
                'controller' => 'user', 'action' => 'show', 'id' => $user->id
            ));
        }
    }

    $view = $this->getView();
    $view->assign('user', $user);
    $view->display('user/create.tpl');
}