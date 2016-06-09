<?php

namespace Controllers;

class ContributionController extends \Picon\Lib\Controller{
    
    public function pre_action(){
        $this->layout   =   "back"; 
        if($this->route["action"] == "listeAdmin"){
            $this->security->check(2);
    }
        parent::pre_action();
        $this->set(array(
                        "pseudo"    =>  $_SESSION["user"]["pseudo"], 
                        "authLevel" =>  $this->security->getAuthLevel()
                    )); 
    }

    // Auth level : 1
    public function listeAction(){
        $_contribution  =   new \Models\ContributionModel();
        $this->set(array(
                        "listeContrib"  =>  $_contribution->getAll($_SESSION["user"]["id"])
                    ));
    }

    // Auth level : 2
    public function listeAdminAction(){
        $_contribution  =   new \Models\ContributionModel();
        if($this->route["method"] == "POST"){
            if(isset($_POST["id"])){
                if(isset($_POST["delete"]) && $_POST["delete"]){
                    $_contribution->delete($_POST["id"]);
                } else if(isset($_POST["validate"]) && $_POST["validate"]){
                    $_contribution->updateValidation($_POST["id"], 1);
                } else if(isset($_POST["refuse"]) && $_POST["refuse"]){
                    $_contribution->updateValidation($_POST["id"], 2);
                }
            } else {
                $this->sendViewError("Bad inputs");
            }
        }
        $this->set(array(
                        "listeContrib"  =>  $_contribution->getAll()
                    ));

    }

}
