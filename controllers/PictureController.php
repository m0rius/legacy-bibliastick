<?php

namespace Controllers;

class PictureController extends \Picon\Lib\Controller{

    public function pre_action(){
        $this->layout   =   "back"; 
        if($this->route["action"] == "listeAdmin"){
            $this->security->check(2);
    }
        parent::pre_action();
        $this->set(array("pseudo" =>  $_SESSION["user"]["pseudo"], "authLevel" =>  $this->security->getAuthLevel())); 
    }

    // Auth level : 1
    public function listeAction(){

    }

    // Auth level : 2
    public function listeAdminAction(){

    }

}
