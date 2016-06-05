<?php

namespace Controllers;

class HomeController extends \Picon\Lib\Controller{

    public function indexAction(){

    }

    public function searchAction(){
        $_stickers  =   new \Models\StickerModel();
        $keyword    =   $_GET["q"];
        $searchedStikers    =   $_stickers->searchStickers($keyword);
        $this->set(array("stickers" => $searchedStikers));
    }
}
