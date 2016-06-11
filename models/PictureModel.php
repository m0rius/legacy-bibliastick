<?php

namespace Models;

class PictureModel extends \Picon\Lib\Model{

    const P_MAX_2GET    =   5;
    const P_COL_DIST    =   0.25;

    public function getStickerPictures($id){
        $query  =   self::$db->prepare("select name, type, color from pictures where id_sticker = ? and validation = 1;");
        $query->execute(array($id));
        return $query->fetchAll();
    }
    
    public function getAllPerSticker($idSticker){
        $query  =   self::$db->prepare("select p.id as id, p.name as name, p.type as type, p.color as color, i.id as legende_id, i.content as legende from pictures as p join infos as i on i.id_picture = p.id where p.validation = 1 && p.id_sticker = ?;");
        $query->execute(array($idSticker));
        return $query->fetchAll();
    }

    public function createNew($name, $legend, $type, $color, $idAuthor, $idSticker){
        $_infos         =   new \Models\InfoModel();
        $_contributions =   new \Models\ContributionModel();
        $query  =   self::$db->prepare("insert into pictures (name, type, color, validation, creation, id_author, id_sticker) values (?, ?, ?, 3, NOW(), ?, ?);");
        $query->execute(array($name, $type, $color, $idAuthor, $idSticker));

        $query  =   self::$db->prepare("select id from pictures where id_author = ? && name = ? order by id desc limit 1;");
        $query->execute(array($idAuthor, $name));
        $id =   $query->fetchAll()[0]["id"];

        $_contributions->createNew($legend, $_infos->createNewForPicture($id, $idAuthor), $idAuthor);

    }

    public function getAll($idAuthor = 0){
        $toReturn   =   array();
        $levels     =   array(
            1   =>  "validated",
            2   =>  "refused",
            3   =>  "waiting"
        );
        foreach($levels as $num => $level){
            $results    =   $this->getPicturesPerValidation($num, $idAuthor);
            if($results){
                $toReturn[$level]   =   $results;
            }
        }
        return $toReturn;
    }

    public function getPicturesPerValidation($lvValidation, $idAuthor = 0){
        $sql    =   "select 
                        p.id as id, p.name as name, p.creation as creation, 
                        p.id_sticker as id_sticker, s.title as name_sticker,
                        u.pseudo as pseudo_author, u.mail as mail_author 
                    from 
                        pictures as p
                    join 
                        users as u on p.id_author = u.id 
                    join 
                        stickers as s on p.id_sticker = s.id
                    where 
                        p.validation = ? " . ($idAuthor ? "&& p.id_author = ? " : "") . ";";
        $args   =   array($lvValidation);
        $idAuthor && $args[] = $idAuthor;
        $query  =   self::$db->prepare($sql);
        $query->execute($args);
        return $query->fetchAll();
    }

    public function delete($id){
        $query  =   self::$db->prepare("select name from pictures where id = ?;");
        $query->execute(array($id));
        $infos  =   $query->fetchAll();

        if($infos){
            $query  =   self::$db->prepare("delete from pictures where id = ?;");
            $query->execute(array($id));

            return unlink(          \Picon\Lib\Config::get_value("ROOT")
                                .   \Picon\Lib\Config::get_value("sticker_folder", "path")
                                .   "/" . $infos[0]["name"]
                            );
        }
    }

    public function deleteAllForSticker($idSticker){
        $query  =   self::$db->prepare("select id from pictures where id_sticker = ?;");
        $query->execute(array($idSticker));
        $infos  =   $query->fetchAll();

        foreach($infos as $info){
            $this->delete($info["id"]);
        }
    }

    public function updateValidation($id, $lvValidation){
        $query  =   self::$db->prepare("update pictures set validation = ? where id = ?;");
        $query->execute(array($lvValidation, $id));
    }

    public function getColorFromFile($file){
        $colors     =   array();
        $imagick    =   new \Imagick($file);
        $imageIter  =   new \ImagickPixelIterator($imagick);

        // First, we get all of the colors and also their occurences
        foreach($imageIter as $pixels){
            foreach($pixels as $pixel){
                $c      =   $pixel->getColor();
                $c      =   "#" 
                            . str_pad(dechex($c["r"]), 2, "0", STR_PAD_LEFT) 
                            . str_pad(dechex($c["g"]), 2, "0", STR_PAD_LEFT) 
                            . str_pad(dechex($c["b"]), 2, "0", STR_PAD_LEFT);
                !isset($colors[$c]) && $colors[$c] = 0;
                $colors[$c] += 1;
            }
        }

        // We sort the array
        arsort($colors);

        // We clean up the semblable colors
        $tmp    =   array();
        $saved  =   null;
        foreach($colors as $color => $num){
            if(!$saved){
                $saved = $color;
                $tmp[$color] = $num;
            } else if(!$this->isColorSemblable($color, $saved)){
                $saved          = $color;
                $tmp[$color]    = $num;
            }
            if(count($tmp) > self::P_MAX_2GET)
                break;
        }
        return $this->concatColor($tmp);
    }

    public function concatColor($arrayColor, $n = null){
        !$n     &&  $n  =   self::P_MAX_2GET;
        return json_encode(array_keys(array_slice($arrayColor, 0, $n)));
    }

    public function isColorSemblable($input, $toCompare){
        $pixel  =   new \ImagickPixel($toCompare);
        return $pixel->isPixelSimilar($input, self::P_COL_DIST);
    }
}
