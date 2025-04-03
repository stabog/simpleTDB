<?php
namespace SimpleUsers;

use SimpleTdb\TextDataBase as TDB;
use SimpleUsers\UsersSess as US;

class Users{
    protected $dbName = 'usrs';
    protected $data = [];
    protected $sess;

    public function __construct(){
        $this->data = TDB::getInstance($this->dbName);
        $this->sess = new US();
    }

    public function getDataUsers()
    {
        return $this->data->all();        
    }

    public function getUser($uId)
    {
        $userInfo = $this->data->get($uId);

        if (!$userInfo) {
            return null;            
        }

        return new User($userInfo);
        
    }

    


    public function getUserIdbyEmail($email)
    {
        foreach ($this->data->all() as $id => $userInfo){
            if ($userInfo[2][0] == $email){
                return $id;
            }
        }
        return null;
    }

    


    public function addUser($info)
    {
        $email = $info[2][0];
        foreach ($this->data->all() as $id => $userInfo){
            if ($userInfo[2][0] == $email){
                $err = true;
                $mess = 'Пользователь с данным email уже существует в базе';
                return [$err, $mess];
            }
        }

        $tobase = [
            2 => [
                0 => $email,
                1 => password_hash($info[2][1], PASSWORD_DEFAULT),
                2 => $info[2][2] ?? 1
            ],
            3   => [
                0 => $info[3][0],
                1 => $info[3][1],
                2 => $info[3][2] ?? ''
            ],
        ];

        $newId = $this->data->add($tobase);
        return [false, $newId];

    }

    

    
    public function viewUserMenu($uId)
    {
        $curUser = $this->getUser($uId);
        
        if (!$curUser){
            return '<a class="ui item" href="/auth/">Войти</a>';
        }

        $html = '
        <div class="ui simple dropdown item">
            '.$curUser->getName("fi").'
            <i class="dropdown icon"></i>
            <div class="menu">
                <a class="item" href="/auth/?act=signout">Выйти</a>
            </div>
        </div>
        ';

        return $html;
    }
    
}