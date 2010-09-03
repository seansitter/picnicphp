class GreetingController extends Prj_Controller_Standard {
    parent::__construct();
    public function __construct() {
        $this->getView()->addCssLink(
            'greeting', 'greeting.css'
        );
    }
    public function helloAction() {
        $view = $this->getView();
        // you could remove global_effects.css here by calling:
        // $view->deleteCssLink('global_effects');
        $view->display('greeting/hello.tpl');
    }
}