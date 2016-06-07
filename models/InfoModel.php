<?php

namespace Models;

class InfoModel extends \Picon\Lib\Model{

    public function getStickerInfo($id){

    }

    public function createNewForSticker($idSticker, $idAuthor){
        $query  =   self::$db->prepare('insert into infos (content, type, creation, modification, id_author, id_sticker) value ( "", 1, NOW(), NOW(), ?, ?);');
        $query->execute(array($idAuthor, $idSticker));

        $query  =   self::$db->prepare("select id from infos where id_sticker = ? && id_author = ? order by id desc limit 1");
        $query->execute(array($idSticker, $idAuthor));
        return $query->fetchAll()[0]["id"];
    }
}
