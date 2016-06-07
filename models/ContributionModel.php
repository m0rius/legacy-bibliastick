<?php

namespace Models;

class ContributionModel extends \Picon\Lib\Model{

    public function createNew($content, $idInfo, $idAuthor){
        $query  =   self::$db->prepare('insert into contributions (content, validation, creation, id_author, id_info) values (?, 3, NOW(), ?, ?);'); 
        $query->execute(array($content, $idAuthor, $idInfo));

    }
}
