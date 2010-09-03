class GreetingController extends Prj_Controller_Standard {
    public function __construct() {
        parent::__construct();
        $this->getView()->setDefaultLayout('layouts/mylayout.tpl');
    }
}
