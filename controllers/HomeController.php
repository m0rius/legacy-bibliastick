<?php

namespace Controllers;

class HomeController extends \Picon\Lib\Controller{

    public function pre_action(){
        $this->security->disable();
        parent::pre_action();
        $this->set(array(
                        "isLoggedIn"    =>  $this->security->isLoggedIn(),
                    ));
    }

    public function indexAction(){
        $this->layout = "";

    }

    public function searchAction(){
        if($this->route["method"] != "GET")
            throw new \Picon\Lib\HttpException(404);
        $type       =   "";
        $keyword    =   isset($_GET["q"])  ? $_GET["q"]  : "";
        $color      =   isset($_GET["c"])  ? $_GET["c"]  : "";
        $date       =   isset($_GET["db"]) ? $_GET["db"] : "";
        
        $date       &&  $type   =   "date";
        $color      &&  $type   =   "color";
        !$type      &&  $type   =   "text";

        $_stickers          =   new \Models\StickerModel();
        $searchedStikers    =   $_stickers->searchStickers($keyword, $type);

        $this->set(array("stickers" => $searchedStikers));
    }

    public function signinAction(){
        if($this->route["method"] == "POST"){
            if(
                    isset($_POST["pseudo"]) && $_POST["pseudo"]
                &&  isset($_POST["email"]) && $_POST["email"]
                &&  isset($_POST["pass"]) && $_POST["pass"]
                &&  isset($_POST["confirmpass"]) && $_POST["confirmpass"]
                &&  $_POST["pass"] == $_POST["confirmpass"]
            ){
                $_users     =   new \Models\UserModel();
                $_users->addUser($_POST["pseudo"], $_POST["email"], 1, $_POST["pass"]);
                $this->sendViewMessage("Merci pour vottre inscription! Un administrateur prendra contact avec vous d'ici quelques jours, stay tuned!");
            }
            else{
                $this->sendViewError("Mauvais remplissage du formulaire");
            }
        }
    }
}
