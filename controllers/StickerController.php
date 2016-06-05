<?php

namespace Controllers;

class StickerController extends \Picon\Lib\Controller{


    public function indexAction($id = 0){
        $_stickers      =   new \Models\StickerModel();
        $stickerInfos   =   $_stickers->getInfosPerId($id);
        if(!$stickerInfos)
            throw new \Picon\Lib\HttpException(404, "Sticker not found");
        $this->set(array("infos"    =>  $stickerInfos));
    }

    public function editAction($id = 0){

    }
}
