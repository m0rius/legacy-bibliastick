<?php

namespace Controller;

class UserController extends \Picon\Lib\Controller{

    public function pre_action(){
        if($this->route["action"]   ==  "login"){
            $this->security->disable();
        }
        parent::pre_action();
    }

    public function indexAction(){

    }

    public function loginAction(){

    }
}
