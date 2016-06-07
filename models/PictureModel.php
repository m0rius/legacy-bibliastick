<?php

namespace Models;

class PictureModel extends \Picon\Lib\Model{


    public function getStickerPictures($id){
        $query  =   self::$db->prepare("select name, type, color from pictures where id_sticker = ? and validation = 1;");
        $query->execute(array($id));
        return $query->fetchAll();
    }
}
