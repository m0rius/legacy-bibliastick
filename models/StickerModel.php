<?php

namespace Models;

class StickerModel extends \Picon\Lib\Model{

    public function getAllStickers(){
        return self::$db->query("select * from stickers;")
                        ->fetchAll();
    }

    public function searchStickers($search, $type = "text"){
        $query  =   self::$db->prepare("select * from stickers where title like '%?%';");
        $query->execute(array($search));
        return $query->fetchAll();

    }

    public function searchStickersPerCatgory($category){

    }

    public function searchStickersPerAuthor($author){

    }
    
    public function searchStickersPerTitle($title){

    }

}
