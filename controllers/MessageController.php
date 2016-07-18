<?php

namespace App\Controllers;

class MessageController extends \Picon\Lib\Controller{

    public function pre_action(){
        $this->layout   =    "back";
        parent::pre_action();
        $this->set(array("pseudo" =>  $_SESSION["user"]["pseudo"], "authLevel" =>  $this->security->getAuthLevel())); 
    }

    public function indexAction(){

    }

    public function conversation($sender, $receiver){

    }
}
