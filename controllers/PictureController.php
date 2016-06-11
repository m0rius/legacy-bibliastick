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

    public function indexAction($filename = ""){
        $path    =      \Picon\Lib\Config::get_value("ROOT")
                    .   \Picon\Lib\Config::get_value("sticker_folder", "path")
                    .   "/" .$filename;
        if(is_file($path)){
           header("Content-type: " . image_type_to_mime_type(exif_imagetype($path)));
           echo file_get_contents($path);
        } else {
            throw new \Picon\Lib\HttpException(404);
        }
    }

    // Auth level : 1
    public function listeAction(){
        $idUser     =   $_SESSION["user"]["id"];
        $_pictures  =   new \Models\PictureModel();
        $this->set(array(
                        "listes"   => $_pictures->getAll($idUser),
                    )
                );
    }

    // Auth level : 2
    public function listeAdminAction(){
        $_pictures  =   new \Models\PictureModel();
        if($this->route["method"]   ==  "POST"){
            if(isset($_POST["id"])){
                if(isset($_POST["delete"]) && $_POST["delete"]){
                    $_pictures->delete($_POST["id"]);
                } else {
                    if(isset($_POST["validation"])) {
                        if($_POST["validation"] == "validate"){
                            $_pictures->updateValidation($_POST["id"], 1);
                        } else if($_POST["validation"] == "refuse"){
                            $_pictures->updateValidation($_POST["id"], 2);
                        }
                    }
                    if(isset($_POST["type"]) && in_array($_POST["type"], array(1,2,3))){
                        if($prevId =   $_pictures->updateType($_POST["id"], $_POST["type"])){
                            $this->sendViewMessage("Attention, le type de la photo n° " . $prevId . " a été modifié!");
                        }
                    }
                }
            } else {
                $this->sendViewError("Bad inputs");
            }
        }
        $this->set(array(
                        "listes" =>  $_pictures->getAll()
                    )
                );
    }

}
