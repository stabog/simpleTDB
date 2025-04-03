<?php
namespace SimpleUsers;


class User{    
    protected $data = [];
    protected $email;    
    protected $hash;
    protected $role;
    protected $name;

    public function __construct($array){
        
        $this->email = $array[2][0] ?? "email";
        $this->hash = $array[2][1] ?? "hash";
        $this->role = $array[2][2] ?? 1;

        if (!is_array($array[3])) $array[3] = explode (" ", $array[3]);
        $this->name = [
            "f" => $array[3][0],
            "i" => $array[3][1],
            "o" => $array[3][2] ?? '',
        ];
    }

    
    public function getPasshash ($pass)
    {
        return password_hash($pass, PASSWORD_DEFAULT);
    }

    public function getName($type='')
    {
        if ($type == '') return implode (" ", $this->name);
        if ($type == 'i') return $this->name['i'];
        if ($type == 'f') return $this->name['f'];
        if ($type == 'if') return $this->name['i'].' '.$this->name['f'];
        if ($type == 'fi') return $this->name['f'].' '.$this->name['i'];
        return '';
    }

    public function getEmail()
    {
        return  $this->email;
    }
    
    public function __get($property)
    {
        if (!isset($this->$property)) return null;
        return $this->$property;
    }
}