<?php

namespace SimpleTdb;

use SimpleTdb\TextDataModel;

class TextDataModelUsers extends TextDataModel
{
    protected $schemItems = [
        'id' => ['id', [], 0, 'Id колонки', true, true, 'text', '', '', [], [], []],
        'ausData' => ['ausData', [], 1, 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
        'email' => ['email', [], 2, 'Email', true, true, 'text', '', '', [], [], []],
        'passHash' => ['passHash', [], 3, 'Хэш Пароля', true, false, 'text', '', '', [], [], []],
        'name' => ['name', [], 4, 'ФИО пользоваталя', true, false, 'list', '', '', [], [], []],
        'role' => ['role', [], 5, 'Роль пользователя', true, false, 'numb', '', '', [], [], []],
        'status' => ['role', [], 6, 'Статус пользователя', true, false, 'numb', '', '', [], [], []],
    ];

    public function __construct(string $dbName = 'users', string $dbPath = '', string $indexType = 'guid')
    {
        parent::__construct($dbName, $dbPath, $indexType);        
    }

    public function add($info, $surce="user")
    {
        throw new TextDataModelException("Используйте метод reg для добавления пользователя.");
    }

    public function getUserIdByEmail($email)
    {
        
        $users = $this->all();
        $user = array_filter($users, function ($item) use ($email) {            
            return $item['email'] === $email;
        });
        
        if (empty($user)){
            return false;
        }        

        return current($user)['id'];
    }

    public function reg(array $userInfo)
    {
        if (!isset($userInfo['email'])) {
            throw new TextDataModelException("email не указан");
        }

        if ($this->getUserIdByEmail($userInfo['email'])){
            throw new TextDataModelException("Пользователь с таким email уже зарегистрирован.");
        }

        if (!isset($userInfo['password'])) {
            throw new TextDataModelException("password не указан.");
        }
        if (!isset($userInfo['passwordRepeat']) or $userInfo['passwordRepeat'] != $userInfo['password']) {
            throw new TextDataModelException("passwordRepeat указан не верно.");
        }
        
        $userInfo['passHash'] = password_hash($userInfo['password'], PASSWORD_DEFAULT);
        unset($userInfo['password']);
        unset($userInfo['passwordRepeat']);
        $userInfo['role'] = 1;
        $userInfo['status'] = 2;

        $info = $this->schem->checkValueBySchem($userInfo, "sys");
        $newId = $this->data->add($info);

        if ($newId) {
            $this->updateLinkedBasesNew($newId, $info);
            return $newId;
        }
        

        return false;
    }    
}