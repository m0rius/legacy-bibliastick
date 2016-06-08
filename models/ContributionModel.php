<?php

namespace Models;

class ContributionModel extends \Picon\Lib\Model{

    public function createNew($content, $idInfo, $idAuthor){
        $query  =   self::$db->prepare('insert into contributions (content, validation, creation, id_author, id_info) values (?, 3, NOW(), ?, ?);'); 
        $query->execute(array($content, $idAuthor, $idInfo));

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

    public function getPerValidation($lvValidation, $idAuthor = 0){
        $sql    =   "select c.id as id, s.title as title_sticker, s.id as id_sticker, c.content as contenu, c.creation as date, u.pseudo as pseudo_author, u.mail as mail_author from contributions as c join infos as i on c.id_info = i.id join stickers as s on s.id = i.id_sticker join users as u on c.id_author = u.id where c.validation = ? " . (($idAuthor) ? "&& c.id_author = ? " : "") . ";"; 
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
        $query  =   self::$db->prepare("update contributions set validation = ? where id = ?;");
        $query->execute(array($lvValidation, $id));

    }
}
