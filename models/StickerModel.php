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
                $query  =   self::$db->prepare("
                                        select 
                                            s.id as id, s.title as title, s.validation as validation, 
                                            s.id_author as author, i.content as infos, p.name as picture
                                        from stickers as s 
                                        join infos as i on i.id_sticker = s.id
                                        left join pictures as p on p.id_sticker = s.id
                                        left join effective_categories as e on s.id = e.id_sticker
                                        left join available_categories as a on e.id_category = a.id
                                        where s.validation = 1 && (s.title like ?
                                        or a.name like ? && a.validation = 1) group by s.id");
                $query->execute(array("%" . $search . "%", "%" . $search . "%" ));
                break;
            case "color":
                $_pictures  =   new \Models\PictureModel();
                $query      =   self::$db->query("
                                        select 
                                            s.id as id, s.title as title, s.validation as validation, 
                                            s.id_author as author, i.content as infos, p.name as picture,
                                            p.color as color
                                        from pictures as p 
                                        join stickers as s on s.id = p.id_sticker
                                        join infos as i on i.id_sticker = s.id
                                        where s.validation = 1 && p.validation = 1
                                        ");
                $toReturn   =   array();
                $tmp        =   array();
                foreach($query->fetchAll() as $result){
                    $tmp[$result["id"]][]  =   $result;
                }
                foreach($tmp as $sticker => $pictures){
                    foreach($pictures as $picture){
                        $found  =   false;
                        $colors = json_decode($picture["color"]);
                        foreach($colors as $color){
                            $_pictures->isColorSemblable($search, $color) && $found = true;
                        }
                        if($found){
                            $toReturn[] =   $picture;
                        }
                    }
                }
                return $toReturn;
                break;
            case "date":
                $query  =   self::$db->prepare("
                                        select 
                                            s.id as id, s.title as title, s.validation as validation, 
                                            s.id_author as author, i.content as infos, p.name as picture
                                        from stickers as s 
                                        join infos as i on i.id_sticker = s.id
                                        left join pictures as p on p.id_sticker = s.id
                                        left join effective_categories as e on s.id = e.id_sticker
                                        left join available_categories as a on e.id_category = a.id
                                        where s.validation = 1 && DATE(s.creation) > ? group by s.id order by s.creation desc;");
                $query->execute(array($search));

                break;
            default:
                throw new \Picon\Lib\HttpException(500, "Bad function usage");
                break;
        }
        return $query->fetchAll();
    }

    public function getOne($id){
        $toReturn   =   array();
        $query      =   self::$db->prepare("select * from stickers where id = ? && validation = 1;");
        $query->execute(array($id));
        $toReturn   =   $query->fetch();   
        $query->closeCursor();
        return $toReturn;
    }

    public function getAll($idAuthor = 0){
        $toReturn   =   array();
        $levels     =   array(
            1   =>  "validated",
            2   =>  "refused",
            3   =>  "waiting"
        );
        foreach($levels as $num => $level){
            $results    =   $this->getStickersPerValidation($num, $idAuthor);
            if($results){
                $toReturn[$level]   =   $results;
            }
        }
        return $toReturn;
    }

    public function getStickersPerValidation($lvValidation, $idAuthor = 0){
        $sql    =   "select s.id as id, s.title as title, s.creation as creation, u.pseudo as pseudo_author, u.mail as mail_author from stickers as s join users as u on s.id_author = u.id where s.validation = ? " . ($idAuthor ? "&& s.id_author = ? " : "") . ";";
        $args   =   array($lvValidation);
        $idAuthor && $args[] = $idAuthor;
        $query  =   self::$db->prepare($sql);
        $query->execute($args);
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


    public function delete($id){
        $_pictures   =   new \Models\PictureModel();
        $_pictures->deleteAllForSticker($id);

        $query  =   self::$db->prepare("delete from stickers where id = ?;");
        return $query->execute(array($id));
    }

    public function updateValidation($id, $lvValidation){
        $query  =   self::$db->prepare("update stickers set validation = ? where id = ?;");
        $query->execute(array($lvValidation, $id));
    }

    public function isFavorite($id, $idUser){
        $query  =   self::$db->prepare("select * from liste_stickers where id_user = ? && id_sticker = ?;");
        $query->execute(array($idUser, $id));
        return count($query->fetchAll());
    }

    public function setFavorite($id, $idUser){
        if($this->isFavorite($id, $idUser)){
            $query  =   self::$db->prepare("delete from liste_stickers where id_user = ? && id_sticker = ?;");
            $query->execute(array($idUser, $id));
        } else {
            $query  =   self::$db->prepare("insert into liste_stickers (id_user, id_sticker) values (?, ?);");
            $query->execute(array($idUser, $id));
        }
    }

}
