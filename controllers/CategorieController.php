<?php

namespace Controllers;

class CategorieController extends \Picon\Lib\Controller{

    
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
        $idUser         =   $_SESSION["user"]["id"];
        $_categories    =   new \Models\CategoryModel();
        if($this->route["method"] == "POST"){
            if(isset($_POST["title"]) && $_POST["title"]){
                $_categories->createNew($_POST["title"], (isset($_POST["parent"]) ? $_POST["parent"] : 0), $idUser);
            }
        }
        $this->set(array(
                        "listes"    =>  $_categories->getAll($idUser),
                        "fulliste"  =>  $_categories->getFullList()
                    )
                );
    }

    // Auth level : 2
    public function listeAdminAction(){
        $idUser         =   $_SESSION["user"]["id"];
        $_categories    =   new \Models\CategoryModel();
        if($this->route["method"] == "POST"){
            if(isset($_POST["id"])){
                if(isset($_POST["delete"]) && $_POST["delete"]){
                    $_categories->delete($_POST["id"]);
                } else if(isset($_POST["validation"])) {
                    if($_POST["validation"] == "validate"){
                        $_categories->updateValidation($_POST["id"], 1);
                    } else if($_POST["validation"] == "refuse"){
                        $_categories->updateValidation($_POST["id"], 2);
                    }
                }
            } else {
                $this->sendViewError("Bad inputs");
            }
        }
        $this->set(array(
                        "listes"    =>  $_categories->getAll(),
                        "fulliste"  =>  $_categories->getFullList()
                    )
                );

    }

}
