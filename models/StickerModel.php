<?php

namespace Models;

class StickerModel extends \Picon\Lib\Model{

    public function getAllStickers(){
        return self::$db->query("select * from stickers;")
                        ->fetchAll();
    }

    public function searchStickers($search, $type = "text"){
        switch ($type) {
            case "text":
                $query  =   self::$db->prepare("select s.id, s.title, s.validation, s.id_author from stickers as s 
                                                left join effective_categories as e on s.id = e.id_sticker
                                                left join available_categories as a on e.id_category = a.id
                                                where s.validation = 1 && (s.title like ?
                                                or a.name like ? && a.validation = 1)");
                $query->execute(array("%" . $search . "%", "%" . $search . "%" ));
                break;
            case "date":
                break;
            case "color":
                break;
            default:
                throw new \Picon\Lib\HttpException(500, "Bad function usage");
                break;
        }
        return $query->fetchAll();
    }

    public function getInfosPerId($id){
        $infos  =   array();
        
        $query  =   self::$db->prepare("select * from stickers where id = ? ;");
        $query->execute(array($id));
        $infoSticker    =   $query->fetchAll();
        if($infoSticker){
            $infos["sticker"] = $infoSticker[0];
        }

        return $infos;


    }


}
