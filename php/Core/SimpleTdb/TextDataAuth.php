<?php

namespace SimpleTdb;

use SimpleTdb\TextDataModelUsers;
use SimpleTdb\TextDataModelSessions;

use SimpleTdb\TextDataModelException;

class TextDataAuth
{
    protected $dbPath;
    protected $usersModel;
    protected $sessionsModel;

    public function __construct($dbPath)
    {
        $this->usersModel = new TextDataModelUsers('users', $dbPath, 'guid');
        $this->sessionsModel = new TextDataModelSessions('sess', $dbPath);

        if (count($this->usersModel->all()) == 0){
            $userInfo = [
                'email' => 'admin@domain.com',
                'password' => 'password',
                'passwordRepeat' => 'password',
                'role' => 50,
            ];
            $this->register($userInfo);
        }
    }

    public function register(array $userInfo)
    {
        $userId = $this->usersModel->reg($userInfo);
        return $userId;
    }
    
    public function changePass(array $userInfo)
    {
        return $this->usersModel->changePass($userInfo);
    }

    public function signin(array $loginInfo)
    {
        
        if (!isset($loginInfo["email"]) || !isset($loginInfo["password"])){
            throw new TextDataModelException("Не указаны email или password.");
        }

        $email = $loginInfo["email"] ?? '';
        $password = $loginInfo["password"] ?? '';
        $userId = $this->usersModel->getUserIdByEmail($email);

        if(!$userId){
            throw new TextDataModelException("Пользователь с указанными данными не найден.");
        }

        $user = $this->usersModel->getSysInfo($userId);
        $hash = $user["passHash"] ?? '';

        
        /*
        echo $password;
        echo "\r\n";        
        echo password_hash($password, PASSWORD_BCRYPT);
        echo "\r\n!";
        echo $hash;
        */
        
        
        if(!password_verify($password, $hash)){
            throw new TextDataModelException("Пароль неправильный.");
        }

        $sessId = $this->sessionsModel->createSession($user['id']);
        return $this->sessionsModel->getSession($sessId);
    }

    public function signout($sessId='')
    {
        
        if (!$sessId) {
            throw new TextDataModelException("id сессии не указан");
        }

        return $this->sessionsModel->delSession($sessId);
    }

    public function getSession($sessId='')
    {  
        if (!$sessId) {
            throw new TextDataModelException("id сессии не указан");
        }

        return $this->sessionsModel->getSession($sessId);

    }
    
    
}