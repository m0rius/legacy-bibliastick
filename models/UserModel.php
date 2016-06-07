<?php

namespace Models;

class UserModel extends \Picon\Lib\Model{
   
    /** TODO : crypt those passwords **/
    public function checkInfos($pseudo, $pass){
        $query  =   self::$db->prepare("select type from users where pseudo = ? && pass = ?;");
        $query->execute(array($pseudo, $pass));
        $type   =   $query->fetchColumn(0);
        $query->closeCursor();
        return $type;
    }

    public function getAllValidatedUsers(){
        return self::$db->query("select * from users where validation = 1;")
                        ->fetchAll();
    }

    public function getAllToValidateUsers(){
        return self::$db->query("select * from users where validation = 3;")
                        ->fetchAll();
    }

    public function deleteUser($id){
        $query  =   self::$db->prepare("delete from users where id = ? ;");
        return $query->execute(array($id));
    }

    public function addUser($pseudo, $mail, $type, $pass, $validation = 3){
        $query  =   self::$db->prepare("insert into users(pseudo, mail, type, pass, validation) values (?, ?, ?, ?, ?)");
        return $query->execute(array($pseudo, $mail, $type, $pass, $validation));
    }

    public function updateUser($id, $pseudo, $mail, $type){
        $query  =   self::$db->prepare("update users set pseudo = ?, mail = ?, type = ? where id = ?;");
        return $query->execute(array($pseudo, $mail, $type, $id));
    }

    public function updatePasswordUser($id, $pass){
        $query  =   self::$db->prepare("update users set pass = ? where id = ?;");
        return $query->execute(array($pass, $id));
    }

    public function validateUser($id, $toValidate){
        $v  =   $toValidate ? 1 : 2;
        $query  =   self::$db->prepare("update users set validation = ? where id = ? ;");
        return $query->execute(array($v, $id));
    }

}
