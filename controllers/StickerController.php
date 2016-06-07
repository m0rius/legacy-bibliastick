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
            $this->set(array("pseudo" =>  $_SESSION["user"]["pseudo"], "authLevel" =>  $this->security->getAuthLevel())); 
        }
    }

    public function indexAction($id = 0){
        $_stickers      =   new \Models\StickerModel();
        $stickerInfos   =   $_stickers->getInfosPerId($id);
        if(!$stickerInfos)
            throw new \Picon\Lib\HttpException(404, "Sticker not found");
        $this->set(array("infos"    =>  $stickerInfos));
    }

    public function editAction($id = 0){

    }

    public function listeAction($type = 0){

    }

    Public function listeAdminAction(){
        $this->layout   =   "back";

    }


}
