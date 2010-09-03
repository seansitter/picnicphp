class GreetingController extends Prj_Controller_Standard {
    public function helloAction() {
        $view = $this->getView();
        $date = array('month' => 'January', 'year' => '2009');
        $user = new User();
        $user->name = 'sammy';
        $view->assign('user', $user);
        $view->assign('date', $date);
        $view->display('greeting/hello.tpl');
    }
}