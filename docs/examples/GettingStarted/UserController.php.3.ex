function showAction() {
    $view = $this->getView();
    $id = $this->getParam('id');
    if (!empty($id)) {
		Pfw_Loader::loadModel('User');
		$user = new User($id);
		$user->retrieve();
        $view->assign('user', $user);
    }
    $view->display('user/show.tpl');
}
