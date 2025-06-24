<?php

namespace SimpleTdb;

use SimpleTdb\TextDataModel;

class TextDataModelUsers extends TextDataModel
{
    protected $schemItems = [
        0 => [0, [], 'id', 'Id колонки', true, true, 'text', '', '', [], [], []],
        1 => [1, [], 'ausData', 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
        2 => [2, [], 'email', 'Email', true, true, 'text', '', '', [], [], []],
        3 => [3, [], 'passHash', 'Хэш Пароля', true, false, 'text', '', '', [], [], []],
        4 => [4, [], 'name', 'ФИО пользоваталя', true, false, 'list', '', '', [], [], []],
        5 => [5, [], 'role', 'Роль пользователя', true, false, 'numb', '', '', [], [], []],
        6 => [6, [], 'status', 'Статус пользователя', true, false, 'numb', '', '', [], [], []],
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
            $item['email'] = $item['email'] ?? null;
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

        if (!isset($userInfo['password']) or $userInfo['password'] === '') {
            throw new TextDataModelException("password не указан.");
        }
        if (!isset($userInfo['passwordRepeat']) or $userInfo['passwordRepeat'] != $userInfo['password']) {
            throw new TextDataModelException("passwordRepeat указан не верно.");
        }

        $userInfo['passHash'] = password_hash($userInfo['password'], PASSWORD_BCRYPT);

        unset($userInfo['password']);
        unset($userInfo['passwordRepeat']);
        $userInfo['role'] = $userInfo['role'] ?? 1;
        $userInfo['status'] = $userInfo['status'] ?? 2;

        //$info = $this->schem->checkValueBySchem($userInfo, "sys");
        $info  = $this->schem->validateAndConvertItemValues ($userInfo, $this->schem->getSchem(), "dict", "data", $schemUpdate=false, $showHash=true);        
        $newId = $this->data->add($info);

        if ($newId) {
            //$this->updateLinkedBasesNew($newId, $info);
            return $newId;
        }
        

        return false;
    }
    
    public function changePass(array $data)
    {        
        if (!isset($data['id'])) {
            throw new TextDataModelException("id пользователя не указан");
        }

        if (!isset($data['password']) or $data['password'] === '') {
            throw new TextDataModelException("password не указан.");
        }
        
        $userInfo = $this->get($data['id']);
        if (!$userInfo) {
            throw new TextDataModelException("Пользователь не найден.");
        }

        $userInfo['passHash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $info  = $this->schem->validateAndConvertItemValues ($userInfo, $this->schem->getSchem(), "dict", "data", $schemUpdate=false, $showHash=true);        
        return $newId = $this->data->upd($data['id'], $info);
        
        
    }
    
    public function getSysInfo($id)
    {        
        if (!$id ) {
            throw new TextDataModelException("ID не указан.");
        }

        $item = $this->data->get($id);
        if (!$item){
            throw new TextDataModelException("Элемент '$id' не найден в базе.");
            return false;
        }
        
        $item = $this->schem->validateAndConvertItemValues ($item, $this->schem->getSchem(), "data", "dict", $schemUpdate=false, $showHash=true);
        return $item;
    }
    
    
}