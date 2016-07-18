<?php

namespace App\Models;

class ContributionModel extends \Picon\Lib\Model{

    public function createNew($content, $idInfo, $idAuthor){
        $query  =   self::$db->prepare('insert into contributions (content, validation, creation, id_author, id_info) values (?, 3, NOW(), ?, ?);'); 
        $query->execute(array(trim($content), $idAuthor, $idInfo));

    }

    public function getAll($idAuthor    =   0){
        $toReturn   =   array();
        $levels     =   array(
            3   =>  "waiting",
            1   =>  "validated",
            2   =>  "refused"
        );
        foreach($levels as $num => $level){
            $results    =   $this->getPerValidation($num, $idAuthor);
            if($results){
                $toReturn[$level]   =   $results;
            }
        }
        return $toReturn;
    }

    public function getAllAwaitingPerSticker($idSticker){
        $query  =   self::$db->prepare("select c.id as id, c.content as content, DATE(c.creation) as date,
                                        u.pseudo as pseudo_author, u.mail as mail_author
                                        from contributions as c
                                        join infos as i on i.id = c.id_info
                                        join users as u on u.id = c.id_author
                                        where c.validation = 3 && i.id_sticker = ?
                                        order by id desc;");
        $query->execute(array($idSticker));
        return $query->fetchAll();
    }

    public function getPerValidation($lvValidation, $idAuthor = 0){
        $sql    =   "   select c.id as id, c.content as contenu, c.creation as date,
                        s.title as title_sticker, s.id as id_sticker, 
                        p.id as id_picture, p.name as name_picture,
                        u.pseudo as pseudo_author, u.mail as mail_author 
                        from contributions as c 
                            join infos as i on c.id_info = i.id 
                            join users as u on c.id_author = u.id 
                            left join pictures as p on p.id = i.id_picture
                            left join stickers as s on s.id = i.id_sticker 
                        where 
                            c.validation = ? " 
                    . (($idAuthor) ? "&& c.id_author = ? " : "") 
                    . ";"; 
        $args   =   array($lvValidation);
        $idAuthor && $args[] =  $idAuthor;

        $query  =   self::$db->prepare($sql);
        $query->execute($args);
        return $query->fetchAll();
    }

    public function delete($id){
        $query  =   self::$db->prepare("delete from contributions where id = ?;");
        return $query->execute(array($id));
    }

    public function updateValidation($id, $lvValidation){
        $query  =   self::$db->prepare("select content, validation, id_info from contributions where id = ?;");
        $query->execute(array($id));
        $currentInfos   =   $query->fetchAll();
        $currentInfos   =   !$currentInfos ? array() : $currentInfos[0];

        $query  =   self::$db->prepare("update contributions set validation = ? where id = ?;");
        $query->execute(array($lvValidation, $id));

        // When we validate a contrib previously waiting for valdation, we update info content
        if($lvValidation == 1 && isset($currentInfos["validation"]) && $currentInfos["validation"] == 3){
            $_infos =   new \Models\InfoModel();

            $query  =   self::$db->prepare("select content, validation, id_info from contributions where id = ?;");
            $query->execute(array($id));

            $infosContrib  =   $query->fetchAll()[0];
            $_infos->updateContent($infosContrib["id_info"], $infosContrib["content"]);
        }
        /*  If we refuse a contrib previously validated, we rollback info content 
            with the previous contib content */
        if($lvValidation == 2 && isset($currentInfos["validation"]) && $currentInfos["validation"] == 1 ){
            $_infos =   new \Models\InfoModel();
            $query  =   self::$db->prepare("select content from contributions where id_info = ? && validation = 1 order by id desc limit 1;");
            $query->execute(array($currentInfos["id_info"]));

            $tmp        =   $query->fetchAll();
            $idInfo     =   $currentInfos["id_info"];
            // If there was no previous contrib on this info
            $content    =   (count($tmp)) ? $tmp["content"] : "";

            $_infos->updateContent($idInfo, $content);
        }
    }

}
