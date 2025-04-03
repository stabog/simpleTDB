<?php
namespace FileSearcher;

use SimpleTdb\TextDataBaseModel as TDBM;


class Uploads extends TDBM {
    protected $dbName = 'uploads';
    protected $indexType = "guid";
    protected $uploadDir = 'uploads/';

    protected $schemItems = [
        1 => [1, '', 'id', 0, 2, true, 'numb', '', 1, 2, 'Id', '', '', '', 1, '', [], [], []],
        2 => [2, '', 'sys', 1, 2, false, 'arra', '', 1, 2, 'Системные поля', '', '', '', 2, '', [], [], []],
        3 => [3, '', 'newName', 2, 1, true, 'text', '', 1, 4, 'Новое имя', '', '', '', 3, '', [], [], []],
        4 => [4, '', 'origName', 3, 1, false,  'text', '', 1, 4, 'Оригинальное имя', '', '', '', 4, '', [], [], []],
        5 => [5, '', 'url', 4, 1, false,  'text', '', 1, 4, 'Ссылка', '', '', '', 4, '', [], [], []],
        6 => [6, '', 'numb', 5, 1, false, 'text', '', 11, 4, 'Тип', '', '', '', 6, '', [], [], []],        
        7 => [7, '', 'size', 6, 1, false, 'numb', 2, 1, 4, 'Размер', '', '', '', 7, '', [], [], []],
        8 => [8, '', 'width', 7, 1, false, 'numb', 1, 1, 4, 'Ширина', '', '', '', 7, '', [], [], []],
        9 => [9, '', 'height', 8, 1, false, 'numb', 1, 1, 4, 'Высота', '', '', '', 7, '', [], [], []],
        10 => [10, '', 'duration', 9, 1, false, 'numb', 1, 1, 4, 'Длительность', '', '', '', 7, '', [], [], []],
    ];
    

    public $types = [
        1 => ["Изображение"],
        2 => ["Видео"],
        3 => ["Аудио"],
        4 => ["Архив"],
        5 => ["Документ"],
    ];


    public function deldel ($id)
    {        
        $lastInfo = $this->data->get($id);        
        
        if ($lastInfo and file_exists($lastInfo[2])){
            unlink($lastInfo[2]);
        
            if ($lastInfo[5] == 'image'){
                $fileName = basename($lastInfo[2]);
                $imgMedium = $this->uploadDir.'m/'.$fileName;
                $imgSmall = $this->uploadDir.'s/'.$fileName;

                if (file_exists($imgMedium))  unlink($imgMedium);
                if (file_exists($imgSmall))  unlink($imgSmall);
            }

        }

        if ($this->data->del($id)){
            $this->updateLinkedBasesNew($id, [], $lastInfo);
            return true;
        }

        return false;
    }

    /*
    public function del($id, $checkLinks=true)
    {        
        $info = [];
        $lastInfo = $this->data->get($id);
        
        if ($checkLinks and $this->data->del($id)){
            $this->updateLinkedBasesNew($id, [], $lastInfo);
            return true;
        }

        return false;
    }
        */
}