<?php

namespace Controllers;

class UserController extends \Picon\Lib\Controller{

    public function pre_action(){
        if($this->route["action"]   ==  "login"){
            $this->security->disable();
        }
        parent::pre_action();
    }

    public function indexAction(){

    }

    public function loginAction($deco = ""){
        $error  =   false;
        if($this->route["method"] == "POST"){
            if(
                isset($_POST["pseudo"]) && $_POST["pseudo"] && 
                isset($_POST["pass"]) && $_POST["pass"]
            ){
                $_users     =   new \Models\UserModel();      
                $auth_level =   $_users->checkInfos($_POST["pseudo"], $_POST["pass"]);
                if($auth_level){
                    $this->security->setSessionInfos($auth_level, $_POST["pseudo"], $_POST["pass"]);
                    $this->redirect("/back/home");
                }
            }
            $error  =   true;
        }
        $this->set(array("error"   =>  $error, "fromLogout" => $deco && true));
    }

    public function logoutAction(){
       $this->security->unsetSessionInfos(); 
       $this->redirect("/back/login/success");

    }
}
