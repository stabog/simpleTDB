<?php
namespace FileSearcher;

use SimpleTdb\TextDataBase as TDB;

class File{    
    protected $id;
    protected $sysInfo;


    protected $name;
    protected $ext;
    protected $path;
    protected $type;
    protected $size;
    protected $dateCreate;
    protected $dateModify;
    protected $parrent;

    protected $rootPath;


    public function __construct(array $info){
        $this->id = $info[0];
        $this->sysInfo = $info[1];
        
        $this->name = $info[2] ?? '';
        $this->ext = $info[3] ?? '';
        $this->type = $info[4] ?? '';

        $normalizedPath = (isset($info[5])) ? str_replace('\\', '/', $info[5]) : '';
        $this->path = $normalizedPath;

        $this->size = $info[6] ?? '';
        $this->dateCreate = $info[7] ?? '';
        $this->dateModify = $info[8] ?? '';
        $this->parrent = $info[9] ?? '';

        $normalizedPath = str_replace('\\', '/', $this->path);

        $fileInfo = pathinfo($this->path);
        $this->rootPath = $fileInfo['dirname'];
        
        
    }


    public function __get($property)
    {
        if (!isset($this->$property)) return null;
        return $this->$property;
    }    
    
    
}