<?php
namespace SimpleUsers;

use SimpleTdb\TextDataBase as TDB;
use StringHelper as SH;

class UsersAuth {
    protected $post = [];
    protected $users;
    protected $sess;

    public function __construct(Users $users, Sessions $sess)
    {        
        if (isset($_POST["authData"])){
            $this->post = $_POST["authData"];
        }

        $this->users = $users;
        $this->sess = $sess;
        
    }
    
    public function run ($act, $id=null)
    {        
        $html = '';

        if ($act == 'signin'){
            
            [$userId, $mess] = $this->trySignIn();
            if (!$userId){
                $_SESSION["message"][] = ["err", $mess];
                $html .= $this->viewSignForm(); 
            } else {
                $_SESSION["message"][] = ["suc", $mess];
                $html .= $this->viewSignForm();
                $_SESSION["curUserId"] = $userId;
                
                $url = (isset($this->post[2]) and $this->post[2] != '')? $this->post[2] : '/';
                header("Location: $url");
                die();
                
            }

        } else if ($act == 'signout'){
            
            $this->signOut();
            header("Location: /");
            die();

        } else {

            $html .= $this->viewSignForm();
            
        }

        return $html;
    }


    protected function trySignIn()
    {        

        if (count($this->post) == 0){
            return [null, "Данные формы не получены"];
        }        

        $email = trim(strtolower($this->post[0]));
        if (strlen ($email) == 0){
            return [null, "Email написан с ошибками"];
        }
        
        $cureInfo = null;
        foreach ($this->users->getDataUsers() as $userInfo){
            if ($userInfo[2][0] == $email){
                $cureInfo = $userInfo;
                break;
            }
        }

        if (!$cureInfo){
            return [null, 'Пользователь с email '.$email.' не найден'];
        }

        $pass = $this->post[1];
        $hash = $cureInfo[2][1];        
        if (!password_verify($pass, $hash)){
            return [null, 'Пароль указан не верно'];
        }
        
        $userId = $cureInfo[0];
        return [$userId, 'Пользователь авторизовался'];
        
    }



    protected function signOut()
    {
        if (isset($_SESSION["curUserId"])){
            unset($_SESSION["curUserId"]);
        }
        echo 'signOut done';
        $this->sess->run("clean");        
    }



    protected function viewSignForm()
    {
        
        $email = $this->post[0] ?? '';
        $pass = $this->post[1] ?? '';
        $reffLink = $_SERVER['HTTP_REFERER'] ?? '';
        $reff = $this->post[2] ?? $reffLink;
        
        $html = '
        <div class="ui middle aligned center aligned grid">
            <div class="column">                
                <form class="ui large form initial" action="?act=signin" class="ui form" method="post">

                <div class="ui stacked segment">
                    <div class="field">
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="text" name="authData[0]" placeholder="E-mail" value="'.$email.'">
                    </div>
                    </div>
                    <div class="field">
                    <div class="ui left icon input">
                        <i class="lock icon"></i>
                        <input type="password" name="authData[1]" placeholder="Пароль" value="'.$pass.'">
                    </div>
                    </div>
                    <input type="hidden" name="authData[2]" value="'.$reff.'" />

                    <button type="submit" class="ui fluid large green submit button">Войти</button>
                </div>

                </form>
            </div>
        </div>
        
        
        ';

        return $html;
    }
    
}