<?php

namespace App\Controllers;

class ErrorNotfoundController extends \Picon\Lib\Controller{

    public function pre_action(){
        $this->security->disable();
        parent::pre_action();
    }
    public function indexAction(){

    }
}
