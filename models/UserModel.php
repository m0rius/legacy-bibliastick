<?php

namespace Models;

class UserModel extends \Picon\Lib\Model{
   
    /** TODO : crypt thos passwords **/
    public function checkInfos($pseudo, $pass){
        $query  =   self::$db->prepare("select type from users where pseudo = ? && pass = ?;");
        $query->execute(array($pseudo, $pass));
        $type   =   $query->fetchColumn(0);
        $query->closeCursor();
        return $type;
    }

}
