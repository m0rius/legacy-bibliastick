<?php

namespace Controllers;

class StickerController extends \Picon\Lib\Controller{

    public function pre_action(){
        if($this->route["action"] == "index"){
            $this->security->disable(); 
        }

        if($this->route["action"] == "listeAdmin"){
            $this->security->check(2);
        }

        parent::pre_action();

        if(in_array($this->route["action"], array("listeAdmin", "liste"))){
            $this->layout   =   "back";
            $this->set(
                    array(
                            "pseudo"        =>  $_SESSION["user"]["pseudo"], 
                            "authLevel"     =>  $this->security->getAuthLevel()
                        )); 
        }
        $this->set(array(
                        "isLoggedIn"    =>  $this->security->isLoggedIn(),
                    ));
    }

    public function indexAction($id = 0){
        $_stickers      =   new \Models\StickerModel();
        $_infos         =   new \Models\InfoModel();
        $_pictures      =   new \Models\PictureModel();
        $stickerInfos   =   $_stickers->getOne($id);
        if(!$stickerInfos)
            throw new \Picon\Lib\HttpException(404, "Sticker not found");
        $this->set(array(
                        "sticker"       =>  $stickerInfos,
                        "infos"         =>  $_infos->getOnePerSticker($id),
                        "pictures"      =>  $_pictures->getAllPerSticker($id),
                    ));
    }

    public function editAction($id = 0){
        $_stickers      =   new \Models\StickerModel();
        $stickerInfos   =   $_stickers->getOne($id);
        if(!$stickerInfos)
            throw new \Picon\Lib\HttpException(404, "Sticker not found");

        if($this->route["method"] == "POST"){
            if(isset($_POST["editaction"])){

                switch($_POST["editaction"]) {

                    case "add-picture":
                        $this->AddPictureHandler($id);
                        break;
                    case "edit-legende":
                        $this->EditPictureLegendHandler();
                        break;
                    case "edit-information":
                        $this->EditStickerInformationHandler();
                        break;
                    case "add-category":
                        $this->AddCategoryHandler();
                        break;
                    default:
                        $this->sendViewError("Bad inputs");
                        break;
                }
            } else {    
                $this->sendViewError("Bad inputs");
            }
        }

        $_infos         =   new \Models\InfoModel();
        $_pictures      =   new \Models\PictureModel();

        $this->set(array(
                        "sticker"       =>  $stickerInfos,
                        "infos"         =>  $_infos->getOnePerSticker($id),
                        "pictures"      =>  $_pictures->getAllPerSticker($id),
                    ));
    }

    private function EditPictureLegendHandler(){

    }

    private function AddPictureHandler($id){
        $_pictures      =   new \Models\PictureModel();      
        $storagePath    =       \Picon\Lib\Config::get_value("ROOT")
                            .   \Picon\Lib\Config::get_value("sticker_folder", "path");

        // Check if need post data are set
        if( isset($_FILES["new_picture_sticker"]) && isset($_POST["description"]) && isset($_POST["type"]) && in_array($_POST["type"], array(2,3))){
            // Check if there are errors
            if($_FILES["new_picture_sticker"]["error"]){
                $errorArray =   array(
                    UPLOAD_ERR_INI_SIZE     =>  "Le fichier envoyé est trop volumineux",
                    UPLOAD_ERR_FORM_SIZE    =>  "Le fichier envoyé est trop volumineux",
                    UPLOAD_ERR_PARTIAL      =>  "Le téléchargement ne s'est pas terminé correctement, veuillez recommencer",
                    UPLOAD_ERR_NO_FILE      =>  "Aucun fichier n'a été téléchargé, veuillez recommencer",
                    UPLOAD_ERR_NO_TMP_DIR   =>  "Erreur interne",
                    UPLOAD_ERR_CANT_WRITE   =>  "Erreur interne",
                    UPLOAD_ERR_EXTENSION    =>  "Erreur interne" 
                );
                $this->sendViewError($errorArray[$_FILES["new_picture_sticker"]["error"]]);

            // Check if storage dir is writable and if a tmp name file exixts
            } else if(  is_dir($storagePath) && is_writable($storagePath)
                        &&  ($tmpName   =   $_FILES["new_picture_sticker"]["tmp_name"]) ) {
                $pictureInfos   =   array(
                    "name"  =>   sha1(time().$_SESSION["user"]["id"]) . image_type_to_extension(exif_imagetype($tmpName))
                );
                move_uploaded_file($tmpName, $storagePath . "/" . $pictureInfos["name"]);
                $pictureInfos["color"]  =   $_pictures->getColorFromFile($storagePath . "/" . $pictureInfos["name"]);
                $_pictures->createNew($pictureInfos["name"], $_POST["description"], $_POST["type"], $pictureInfos["color"], $_SESSION["user"]["id"], $id);
            }
        }
    }

    public function listeAction($type = ""){
        $idUser     =   $_SESSION["user"]["id"];
        $_stickers  =   new \Models\StickerModel();

        if($this->route["method"]   ==  "POST"){
            if(isset($_POST["title"]) && $_POST["title"] && isset($_POST["description"]) && $_POST["description"]){
               $_stickers->createNew($_POST["title"], $_POST["description"], $_SESSION["user"]["id"]); 
            } else {
                $this->sendViewError("Bad inputs");
            }
        }

        $this->set(array(
                        "perso"         =>  true && $type,
                        "listeAuteur"   =>  $type ? array() : $_stickers->getAll($idUser),
                        "listePerso"    =>  $type ? $_stickers->getStickerListe($idUser) : array()
                    )
                );
    }

    public function listeAdminAction(){
        $_stickers  =   new \Models\StickerModel();
        if($this->route["method"]   ==  "POST"){
            if(isset($_POST["id"])){
                if(isset($_POST["delete"]) && $_POST["delete"]){
                    $_stickers->delete($_POST["id"]);
                } else if(isset($_POST["validation"])) {
                    if($_POST["validation"] == "validate"){
                        $_stickers->updateValidation($_POST["id"], 1);
                    } else if($_POST["validation"] == "refuse"){
                        $_stickers->updateValidation($_POST["id"], 2);
                    }
                }
            } else {
                $this->sendViewError("Bad inputs");
            }
        }
        $this->set(array(
                        "listeStickers" =>  $_stickers->getAll()
                    )
                );
    }


}
