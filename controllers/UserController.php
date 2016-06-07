<?php

namespace Controllers;

class UserController extends \Picon\Lib\Controller{

    const   U_ADD   =   1;
    const   U_DEL   =   2;
    const   U_VAL   =   3;
    const   U_UPD   =   4;
    const   U_PAS   =   5;
    const   U_ERR   =   -1; 

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
        $error          =   "";
        $_users         =   new \Models\UserModel();

        if($this->route["method"]   ==  "POST"){
            switch($this->computeAction($_POST)) {
                case self::U_ADD:
                    $_users->addUser($_POST["pseudo"], $_POST["mail"], $_POST["type"], $_POST["pass"]);
                    break;
                case self::U_DEL:
                    $_users->deleteUser($_POST["id"]);
                    break;
                case self::U_VAL:
                    $toValidate =   isset($_POST["validate"]) && $_POST["validate"] == "validate";
                    $_users->validateUser($_POST["id"], $toValidate);
                    break;
                case self::U_PAS:
                    $_users->updatePasswordUser($_POST["id"], $_POST["pass"]);
                case self::U_UPD:
                    $_users->updateUser($_POST["id"], $_POST["pseudo"], $_POST["mail"], $_POST["type"]);
                    break;
                case self::U_ERR:
                    $error  =   "Bad inputs";
                    break;
                default:
                    throw new HttpException(500);
                    break;
            }
        }

        $this->set(
                array(
                    "users"             =>  $_users->getAllValidatedUsers(), 
                    "usersToValidate"   =>  $_users->getAllToValidateUsers(),
                    "error"             =>  $error
                )
            );
    }

    private function computeAction($f){
        if(isset($f["id"])){
            if(isset($f["delete"]) && $f["delete"] == "delete"){
                return self::U_DEL;
            }
            if(
                isset($f["pseudo"]) && isset($f["mail"]) && isset($f["type"]) && isset($f["pass"]) && isset($f["cpass"]) 
                && $f["pseudo"] && $f["mail"]  && $f["type"] 
                && $f["pass"] == $f["cpass"]
            ){
                if($f["pass"]){
                    return ($f["id"] == "0") ? self::U_ADD : self::U_PAS;
                } 
                if($f["id"] != "0"){
                    return   self::U_UPD;
                }
            }
            if(isset($f["mark"]) && $f["mark"]  ==  "toValidate") {
                return  self::U_VAL;
            }
        }
        return self::U_ERR;
    }
}