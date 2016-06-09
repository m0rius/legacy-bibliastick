<?php

namespace Models;

class PictureModel extends \Picon\Lib\Model{


    public function getStickerPictures($id){
        $query  =   self::$db->prepare("select name, type, color from pictures where id_sticker = ? and validation = 1;");
        $query->execute(array($id));
        return $query->fetchAll();
    }
    
    public function getAllPerSticker($idSticker){
        $query  =   self::$db->prepare("select p.id as id, p.name as name, p.type as type, p.color as color, u.content as legende from pictures as p join infos as i on i.id_picture = p.id where p.validation = 1 && p.id_sticker = ?;");
        $query->execute(array($idSticker));
        return $query->fetchAll();
    }
}
