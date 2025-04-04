<?php
namespace FileSearcher;

use SimpleTdb\TextDataBaseModel as TDBM;


class Users extends TDBM {
    protected $dbName = 'users';
    public $schem = [
        1 => [1, '', 'id', 0, 2, true, 'numb', '', 1, 2, 'Id', '', '', 1, '', [], [], []],
        2 => [2, '', 'sys', 1, 2, false, 'arra', '', 1, 2, 'Системные поля', '', '', 2, '', [], [], []],
        3 => [3, '', 'email', 2, 1, true, 'text', '', 1, 4, 'Email', '', '', 3, '', [], [], []],
        4 => [4, '', 'pass', 3, 2, false,  'text', '', 1, 4, 'Пароль', '', '', 4, '', [], [], []],
        5 => [5, '', 'is_admin', 4, 1, false, 'bool', '', 1, 4, 'Является администратором', '', '', 5, '', [], [], []],
        6 => [6, '', 'users_role', 5, 1, false, 'numb', '', 1, 4, 'Роль полозователя', '', '', 6, '', [], [], []],
        7 => [7, '', 'last_login', 6, 1, false, 'time', '', 1, 4, 'Последний логин', '', '', 7, '', [], [], []],

        8 => [8, '', 'fam', 7, 1, false, 'text', '', 1, 4, 'Фамилия', '', '', 8, '', [], [], []],
        9 => [9, '', 'name', 8, 1, false, 'text', '', 1, 4, 'Имя', '', '', 9, '', [], [], []],
        10 => [10, '', 'sname', 9, 1, false, 'text', '', 1, 4, 'Отчество', '', '', 10, '', [], [], []],
        11 => [11, '', 'username', 10, 1, false, 'text', '', 1, 4, 'Отображаемое имя', '', '', 11, '', [], [], []],
        12 => [12, '', 'profile_picture', 11, 1, false, 'arra', '', 1, 4, 'Фото', '', '', 12, '', [], [], []],
        13 => [13, '', 'company', 12, 1, false, 'text', '', 1, 4, 'Компания', '', '', 13, '', [], [], []],
        14 => [14, '', 'job_title', 13, 1, false, 'text', '', 1, 4, 'Должность', '', '', 14, '', [], [], []],
        15 => [15, '', 'bio', 14, 1, false, 'text', '', 1, 4, 'Биография', '', '', 15, '', [], [], []],        
        16 => [16, '', 'phone', 15, 1, false, 'text', '', 1, 4, 'Телефон', '', '', 16, '', [], [], []],
        17 => [17, '', 'city', 16, 1, false,  'text', '', 1, 4, 'Город', '', '', 17, '', [], [], []],
        18 => [18, '', 'show_email', 17, 1, false, 'bool', '', 1, 4, 'Отображать Email', '', '', 18, '', [], [], []],
        
        19 => [19, '', 'progs', 18, 1, false, 'arra', '', 1, 4, 'Участвует в программах', '', '', 19, '', [], [], []],
        20 => [20, '', 'sess_ucha', 19, 1, false, 'arra', '', 1, 4, 'Сессии, в которых участник', '', '', 20, '', [], [], []],        
        21 => [21, '', 'sess_cons', 20, 1, false, 'arra', '', 1, 4, 'Сессии, в которых консультант', '', '', 21, '', [], [], []],
        22 => [22, '', 'sess_speak', 21, 1, false, 'arra', '', 1, 4, 'Сессии, в которых ведущий', '', '', 22, '', [], [], []],
        23 => [23, '', 'them_speak', 22, 1, false, 'arra', '', 1, 4, 'Темы, в которых ведущий', '', '', 23, '', [], [], []],        
        24 => [24, '', 'teams', 23, 1, false, 'arra', '', 1, 4, 'Команды, в которых участвует', '', '', 24, '', [], [], []],

        25 => [25, '', 'hw_assign', 24, 1, false, 'arra', '', 1, 4, 'Назначенные ДЗ', '', '', 25, '', [], [], []],
        26 => [26, '', 'hw_done', 25, 1, false, 'arra', '', 1, 4, 'Выполненые ДЗ', '', '', 26, '', [], [], []],

        27 => [27, '', 'adalo_id', 26, 1, false, 'numb', '', 1, 4, 'Adalo ID', '', '', 26, '', [], [], []],
        
    ];

    public function modifyImportItem($item)
    {        
        $item[2] = strtolower($item[2]);
        if ($item[6] != '' and !is_numeric($item[6])) $item[6] = strtotime($item[6]);
        return $item;
    }
    
}