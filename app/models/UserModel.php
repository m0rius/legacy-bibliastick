<?php

namespace App\Models;

class UserModel extends \Picon\Lib\Model{

    /* TODO: write the prototype of this method :/ what shall it do??   */
    public function checkInfos($pseudo, $pass){
        $query  =   self::$db->prepare("select id, type, pass from users where pseudo = ?;");
        $query->execute(array($pseudo));
        $all    =   $query->fetch();
        if (!$all) return array();
        if (!password_verify($pass,$all['pass'])) {
            return array();
        }
        return $all;
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
        return $query->execute(array($pseudo, $mail, $type, password_hash($pass, PASSWORD_DEFAULT), $validation));
    }

    public function updateUser($id, $pseudo, $mail, $type){
        $query  =   self::$db->prepare("update users set pseudo = ?, mail = ?, type = ? where id = ?;");
        return $query->execute(array($pseudo, $mail, $type, $id));
    }

    public function updatePasswordUser($id, $pass){
        $query  =   self::$db->prepare("update users set pass = ? where id = ?;");
        return $query->execute(array(password_hash($pass, PASSWORD_DEFAULT), $id));
    }

    public function validateUser($id, $toValidate){
        $v  =   $toValidate ? 1 : 2;
        $query  =   self::$db->prepare("update users set validation = ? where id = ? ;");
        return $query->execute(array($v, $id));
    }

}
