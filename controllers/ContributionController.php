<?php

namespace Controllers;

class ContributionController extends \Picon\Lib\Controller{

    
    public function pre_action(){
        $this->layout   =   "back"; 
        if(in_array($this->route["action"], array("listeAdminPicture", "listeAdminCategorie", "listeAdminContribution"))){
            $this->security->check(2);
    }
        parent::pre_action();
        $this->set(array("pseudo" =>  $_SESSION["user"]["pseudo"], "authLevel" =>  $this->security->getAuthLevel())); 
    }

    // Auth level : 1

    public function listePictureAction(){

    }

    public function listeCategorieAction(){

    }

    public function listeContributionAction(){
    
    }

    // Auth level : 2

    public function listeAdminPictureAction(){

    }

    public function listeAdminCategorieAction(){

    }

    public function listeAdminContributionAction(){

    }

}
