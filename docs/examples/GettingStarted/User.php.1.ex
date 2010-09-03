public function validate($save_method) {
    Pfw_Loader::loadClass('Pfw_Validate');
    $v = new Pfw_Validate($this);
    if (Pfw_Model::SAVE_INSERT == $save_method) {
        $v->presence('first_name', "First name is required!");
        $v->presence('last_name', "Last name is required!");
    }
    return $v->success();
}