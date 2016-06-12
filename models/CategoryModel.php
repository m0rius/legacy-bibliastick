<?php

namespace Models;

class CategoryModel extends \Picon\Lib\Model{

    const CAT_TYPE = array(
        "creation"      =>  "available_categories",
        "affectation"   =>  "effective_categories"
    );

    public function createNew($name, $idParent, $idAuthor){
        $query  =   self::$db->prepare("insert into available_categories (name, parent, creation, validation, id_author) values (?, ". ($idParent ? $idParent : "NULL") .", NOW(), 3, ?);");
        $query->execute(array($name, $idAuthor));
    }
    public function getFullList(){
        $query  =   self::$db->query("select id, name from available_categories where validation = 1;");
        return $query->fetchAll();
    }

    public function getAllPerSticker($id){
        $query  =   self::$db->prepare("select a.id as id, a.name as name from available_categories join effective_categories as e on a.id = e.id_category join stickers as s on e.id_sticker = s.id where s.id = ?;");
        $query->execute(array($id));
        return $query->fetchAll();
    }

    public function createNewAffectation($idCategory, $idSticker, $idAuthor){
        $query  =   self::$db->prepare("insert into effective_categories (id_category, id_sticker, id_author, creation, validation) values (?, ?, ?, NOW(), 3);");
        return $query->execute(array($idCategory, $idSticker, $idAuthor));
    }

    public function getAll($idAuthor = 0){
        $toReturn   =   array();
        $levels     =   array(
            1   =>  "validated",
            2   =>  "refused",
            3   =>  "waiting"
        );
        foreach($levels as $num => $level){
            $results    =   $this->getCategoriesPerValidation($num, $idAuthor);
            if($results){
                $toReturn[$level]   =   $results;
            }
        }
        return $toReturn;
    }

    public function getCategoriesPerValidation($lvValidation, $idAuthor = 0){
        $sql    =   "select 
                        c.id as id, c.name as name , cp.name as parent, c.creation as creation,
                        u.pseudo as pseudo_author, u.mail as mail_author 
                    from 
                        available_categories as c
                    join users as u on c.id_author = u.id 
                    left join available_categories as cp on c.parent = cp.id
                    where c.validation = ? " 
                    . ($idAuthor ? "&& c.id_author = ? " : "") 
                    . ";";
        $args   =   array($lvValidation);
        $idAuthor && $args[] = $idAuthor;
        $query  =   self::$db->prepare($sql);
        $query->execute($args);
        return $query->fetchAll();
    }

    public function delete($type, $id){
        $query  =   self::$db->prepare(
                                                "delete from "
                                            .   self::CAT_TYPE[$type]
                                            .   " where id = ?;"
                                        );
        return $query->execute(array($id));
    }

    public function updateValidation($type, $id, $lvValidation){
        $query  =   self::$db->prepare("update " . self::CAT_TYPE[$type] . " set validation = ? where id = ?;");
        return $query->execute(array($lvValidation, $id));
    }

    public function getAllAffectations($idAuthor = 0){
        $toReturn   =   array();
        $levels     =   array(
            1   =>  "validated",
            2   =>  "refused",
            3   =>  "waiting"
        );
        foreach($levels as $num => $level){
            $results    =   $this->getAffectationsPerValidation($num, $idAuthor);
            if($results){
                $toReturn[$level]   =   $results;
            }
        }
        return $toReturn;
    }

    public function getAffectationsPerValidation($lvValidation, $idAuthor = 0){
         $sql    =   "select 
                        e.id as id, c.id as id_cat, c.name as name_cat, 
                        e.creation as creation,
                        s.id as id_sti, s.title as title_sti, 
                        u.pseudo as pseudo_author, u.mail as mail_author 
                    from 
                        effective_categories as e
                    join stickers as s on e.id_sticker = s.id
                    join available_categories as c on e.id_category = c.id
                    join users as u on e.id_author = u.id
                    where e.validation = ? " 
                    . ($idAuthor ? "&& e.id_author = ? " : "") 
                    . ";";
        $args   =   array($lvValidation);
        $idAuthor && $args[] = $idAuthor;
        $query  =   self::$db->prepare($sql);
        $query->execute($args);
        return $query->fetchAll();
    }
}
