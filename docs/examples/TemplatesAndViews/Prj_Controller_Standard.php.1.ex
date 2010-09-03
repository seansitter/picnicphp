class Prj_Controller_Standard extends Pfw_Controller_Standard {
    parent::__construct();
    public function __construct() {
        $this->getView()->addCssLink(
            'global_effects', 'global_effects.css', 
            array('media' => 'screen, projection')
        );
        $this->getView()->addJsLink(
            'global_effects', 'global_effects.js'
        );
    }
}