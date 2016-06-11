<?php

namespace Models;

class CategoryModel extends \Picon\Lib\Model{


    public function createNew($name, $idParent, $idAuthor){
        $query  =   self::$db->prepare("insert into available_categories (name, parent, creation, validation, id_author) values (?, ". ($idParent ? $idParent : "NULL") .", NOW(), 3, ?);");
        $query->execute(array($name, $idAuthor));
    }
    public function getFullList(){
        $query  =   self::$db->query("select id, name from available_categories where validation = 1;");
        return $query->fetchAll();
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

    public function delete($id){
        $query  =   self::$db->prepare("delete from available_categories where id = ?;");
        return $query->execute(array($id));
    }

    public function updateValidation($id, $lvValidation){
        $query  =   self::$db->prepare("update available_categories set validation = ? where id = ?;");
        return $query->execute(array($lvValidation, $id));
    }

}
