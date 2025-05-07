<?php

namespace SimpleTdb;

use SimpleTdb\TextDataModel as TDM;
use getID3;

class TextDataModelRoot extends TDM {
    protected $dbName = 'root';
    protected $indexType = "num";
    protected $filesInDb = [];
    
    protected $schemItems = [
        0 => [1, [], 'id', 'Id Базы', true, true, 'text', '', '', [], [], []],
        1 => [2, [], 'ausData', 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
        2 => [3, [], 'fileName', 'Имя файла базы', true, true, 'text', '', '', [], [], []],
        3 => [4, [], 'baseName', 'Имя базы', false, false, 'text', '', '', [], [], []],        
    ];

    public function __construct(string $dbName='', string $dbPath='', string $indexType='') {
        // Вызов родительского конструктора
        parent::__construct($dbName, $dbPath, $indexType);

        // Получаем список файлов в папке
        $filesInFolder = $this->getTdbFiles();

        // Получаем список файлов из базы данных
        $this->filesInDb = array_column($this->data->all(), 2);

        // Добавляем файлы, которые есть в папке, но отсутствуют в базе
        foreach ($filesInFolder as $file) {
            if (!in_array($file, $this->filesInDb)) {
                $this->data->add(["", "", $file, $file]);
                $this->filesInDb[] = $file;
            }
        }
        
    }

    

    public function add($info, $surce="user")
    {
        if (!$info or !isset($info['fileName'])) {
            throw new TextDataModelException("Не корректные данные для add.");
        }

        if (in_array($info['fileName'], $this->filesInDb)){
            throw new TextDataModelException("База данных с таким именем существует.");
        }

        $info['baseName'] = isset($info['baseName'])? $info['baseName'] : $info['fileName'];        
        $info = $this->schem->validateAndConvertItemValues ($info, $this->schem->getSchem(), "dict", "data");
        $newId = $this->data->add($info);

        if ($newId) {
            return $newId;
        }

        return false;
    }

    public function upd($id, $info, $surce="user", $checkLinks=true)
    {
        throw new TextDataModelException("Метод не поддерживается.");
    }

    public function del($fileName)
    {
        $baseId = false;
        foreach ($this->data->all() as $id => $info){
            if ($info[2] === $fileName) {
                $baseId = $id;
                break;
            }
        }
        

        if ($baseId){
            $dbPath = $this->dbPath ."/". $fileName.'.tdb';
            $dbSchemPath = $this->dbPath ."/". $fileName.'_schem.tdb';
            if (file_exists($dbPath)) unlink ($dbPath);
            if (file_exists($dbSchemPath)) unlink ($dbSchemPath);
            $this->data->del($baseId);
            return true;
        }
        
        return false;
    }


    protected function getTdbFiles() {
        $files = glob($this->dbPath . '/*.tdb');
        $files = array_filter($files, 'is_string');
        $filteredFiles = [];
        foreach (array_map('basename', $files) as $file) {
            if (substr($file, -10) === '_schem.tdb') {
                continue;
            }
            $filteredFiles[] = substr($file, 0, -4);
        }
        return $filteredFiles;
    }
    
}