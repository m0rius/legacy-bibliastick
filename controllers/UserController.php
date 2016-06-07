<?php

namespace Controllers;

class UserController extends \Picon\Lib\Controller{

    public function pre_action(){
        if($this->route["action"]   ==  "login"){
            $this->security->disable();
        }

        // Check user's privileges
        if($this->route["action"]   ==  "listeAdminUser"){
            $this->security->check(2);
        }

        parent::pre_action();
        
        if(!in_array($this->route["action"], array("logout", "login"))){
            $this->set(array("pseudo" =>  $_SESSION["user"]["pseudo"], "authLevel" =>  $this->security->getAuthLevel())); 
        }
    }

    public function indexAction(){
        $this->layout   =   "back";
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
        } else if($this->security->isLoggedIn()) {
            $this->redirect("/back/home");
        }
        $this->set(array("error"   =>  $error, "fromLogout" => $deco && true));
    }

    public function logoutAction(){
       $this->security->unsetSessionInfos(); 
       $this->redirect("/back/login/success");
    }
    
    public function listeAdminAction(){
        $this->layout   =   "back";
        
    }
}
