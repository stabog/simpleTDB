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

        $this->usersModel->setRespFormatToDict([
            "showHash" => true,
        ]);
        $this->sessionsModel->setRespFormatToDict([]);
    }

    public function register(array $userInfo)
    {
        $userId = $this->usersModel->reg($userInfo);        
        return $userId;
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

        $user = $this->usersModel->get($userId);

        if(!password_verify($password, $user["passHash"])){
            throw new TextDataModelException("Пароль неправильный.");
        }

        return $this->sessionsModel->createSession($user['id']);
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