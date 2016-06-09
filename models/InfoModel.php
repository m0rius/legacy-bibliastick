<?php

namespace Models;

class InfoModel extends \Picon\Lib\Model{

    public function createNewForSticker($idSticker, $idAuthor){
        $query  =   self::$db->prepare('insert into infos (content, type, creation, modification, id_author, id_sticker) value ( "", 1, NOW(), NOW(), ?, ?);');
        $query->execute(array($idAuthor, $idSticker));

        $query  =   self::$db->prepare("select id from infos where id_sticker = ? && id_author = ? order by id desc limit 1");
        $query->execute(array($idSticker, $idAuthor));
        return $query->fetchAll()[0]["id"];
    }

    public function updateContent($id, $content){
        $query  =   self::$db->prepare("update infos set content = ? where id = ?;");
        return $query->execute(array(trim($content), $id));
    }

    public function getOnePerSticker($idSticker){
        $query  =   self::$db->prepare("select * from infos where id_sticker = ?");
        $query->execute(array($idSticker));
        $toReturn   =   $query->fetch();
        $query->closeCursor();
        return $toReturn;
        
    }

}
