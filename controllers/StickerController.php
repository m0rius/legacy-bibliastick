<?php

namespace Controllers;

class StickerController extends \Picon\Lib\Controller{

    public function pre_action(){
        if($this->route["action"] == "index"){
            $this->security->disable(); 
        }

        if($this->route["action"] == "listeAdmin"){
            $this->security->check(2);
        }

        parent::pre_action();

        if(in_array($this->route["action"], array("listeAdmin", "liste"))){
            $this->layout   =   "back";
            $this->set(
                    array(
                            "pseudo"        =>  $_SESSION["user"]["pseudo"], 
                            "authLevel"     =>  $this->security->getAuthLevel()
                        )); 
        }
        $this->set(array(
                        "isLoggedIn"    =>  $this->security->isLoggedIn(),
                    ));
    }

    public function indexAction($id = 0){
        $_stickers      =   new \Models\StickerModel();
        $_infos         =   new \Models\InfoModel();
        $_pictures      =   new \Models\PictureModel();
        $stickerInfos   =   $_stickers->getOne($id);
        if(!$stickerInfos)
            throw new \Picon\Lib\HttpException(404, "Sticker not found");
        $this->set(array(
                        "sticker"       =>  $stickerInfos,
                        "infos"         =>  $_infos->getOnePerSticker($id),
                        "pictures"      =>  $_pictures->getAllPerSticker($id),
                    ));
    }

    public function editAction($id = 0){
        if($this->route["method"] == "post"){

        }
        $this->indexAction($id);
    }

    public function listeAction($type = ""){
        $idUser     =   $_SESSION["user"]["id"];
        $_stickers  =   new \Models\StickerModel();

        if($this->route["method"]   ==  "POST"){
            if(isset($_POST["title"]) && $_POST["title"] && isset($_POST["description"]) && $_POST["description"]){
               $_stickers->createNew($_POST["title"], $_POST["description"], $_SESSION["user"]["id"]); 
            } else {
                $this->sendViewError("Bad inputs");
            }

        }

        $this->set(array(
                        "perso"         =>  true && $type,
                        "listeAuteur"   =>  $type ? array() : $_stickers->getAll($idUser),
                        "listePerso"    =>  $type ? $_stickers->getStickerListe($idUser) : array()
                    )
                );
    }

    Public function listeAdminAction(){
        $_stickers  =   new \Models\StickerModel();
        if($this->route["method"]   ==  "POST"){
            if(isset($_POST["id"])){
                if(isset($_POST["delete"]) && $_POST["delete"]){
                    $_stickers->delete($_POST["id"]);
                } else if(isset($_POST["validation"])) {
                    if($_POST["validation"] == "validate"){
                        $_stickers->updateValidation($_POST["id"], 1);
                    } else if($_POST["validation"] == "refuse"){
                        $_stickers->updateValidation($_POST["id"], 2);
                    }
                }
            } else {
                $this->sendViewError("Bad inputs");
            }
        }
        $this->set(array(
                        "listeStickers" =>  $_stickers->getAll()
                    )
                );
    }


}
