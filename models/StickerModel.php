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
            $_infos     =   new \Models\InfoModel();
            $_pictures  =   new \Models\PictureModel();
            
            $infos["sticker"] = $infoSticker[0];
            $infos["infos"]     =   $_infos->getStickerInfo($id);
            $infos["pictures"]  =   $_pictures->getStickerPictures($id);
        }

        return $infos;
    }

    public function getAllStickersPerAuthor($idAuthor){
        $toReturn   =   array();
        $levels     =   array(
            1   =>  "validated",
            2   =>  "refused",
            3   =>  "waiting"
        );
        foreach($levels as $num => $level){
            $results    =   $this->getStickersPerAuthorAndValidation($idAuthor, $num);
            if($results){
                $toReturn[$level]   =   $results;
            }
        }
        return $toReturn;
    }

    public function getStickersPerAuthorAndValidation($id, $lvValidation){
        $query  =   self::$db->prepare("select * from stickers where validation = ? && id_author = ?;");
        $query->execute(array($lvValidation, $id));
        return $query->fetchAll();
    }

    public function getStickerListe($idUser){
        $query  =   self::$db->prepare("select s.* from stickers as s join liste_stickers as l on s.id = l.id_sticker where l.id_user = ?;");
        $query->execute(array($idUser));
        return $query->fetchAll();

    }


    public function createNew($title, $description, $idAuthor){
        $_infos         =   new \Models\InfoModel();
        $_contribution  =   new \Models\ContributionModel();

        $query  =   self::$db->prepare("insert into stickers (title, creation, id_author) values (?, NOW(), ?)");
        $query->execute(array($title, $idAuthor));

        $query  =   self::$db->prepare("select id from stickers where id_author = ? order by id desc limit 1"); 
        $query->execute(array($idAuthor));
        $idSticker  =   $query->fetchAll()[0]["id"];
        $idInfo     =   $_infos->createNewForSticker($idSticker, $idAuthor);
        $_contribution->createNew($description, $idInfo, $idAuthor);
    }


}
