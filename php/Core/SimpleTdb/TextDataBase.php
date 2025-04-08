<?php

namespace SimpleTdb;

use Exception;

class TextDataBase implements TDBInterface
{
    protected $items;
    protected $head = null;
    protected $props = [
        "name" => '',
        "root" => '',
        "path" => '',
        "indexType" => 'num',
        "lastId" => 0,
    ];
    protected $possibleIndexTypes = ["num", "guid", "str"];

    protected $id = '';
    protected $activeUser = 0;

    // Добавляем свойство для длины строки
    protected $stringLength = 6;

    protected static $sep = ["::", "||", "&&", "##", "@@", "%%"];
    protected static $instances = [];

    public static function getInstance($dbName, $dbPath = "", $indexType = ""): self
    {
        $param = $dbName . $dbPath;
        if (!isset(self::$instances[$param])) {
            self::$instances[$param] = new self($dbName, $dbPath, $indexType);
        }
        return self::$instances[$param];
    }

    protected function __construct(string $dbName, string $dbPath, string $indexType)
    {
        $this->props["name"] = $dbName;
        //DO add check Set root folder 
        $dbFolder = ($dbPath != '') ? $dbPath : 'db';
        //$this->props["root"] = $_SERVER['DOCUMENT_ROOT'] . $dbFolder . '/';
        $this->props["root"] = $dbFolder . '/';

        $this->props["path"]  =  $this->props["root"] . $this->props["name"] . '.tdb';

        //DO add check Set root folder 
        if ($indexType != '') {
            if (in_array($indexType, $this->possibleIndexTypes)) $this->props["indexType"]  = $indexType;
        }

        if (isset($_SESSION["curUserId"])){
            $this->activeUser = $_SESSION["curUserId"];
        }


        try {
            $this->makeFileIfNotExists();
            $this->fileRead();
            //$this->props = $data['info'];
            //$this->items = $data['items'];
            //$items_keys = array_keys($data['items']);

        } catch (Exception $e) {
            //echo $e->getMessage();
        }
    }

    public function setActiveUser($id)
    {
        $this->activeUser = $id;
    }

    public function getProps()
    {
        return $this->props;
    }

    public function getValueByKeys(array $data, array $keys)
    {
        $func = function ($data, $curLevel) use (&$func, $keys) {
            $cureKey = $keys[$curLevel];
            if (!isset($data[$cureKey])) {
                return false;
            }

            $cureItem = $data[$cureKey];


            if (count($keys) > $curLevel + 1) {
                $curLevel++;
                $acc = $func($cureItem, $curLevel);
            } else {
                $acc = $cureItem;
            }

            return $acc;
        };


        return $func($data, 0);
    }


    protected function makeFileIfNotExists(): void
    {
        if (!file_exists($this->props["root"])) mkdir($this->props["root"], 0777, true);
        if (!file_exists($this->props["path"])) {
            $f = fopen($this->props["path"], 'w');
            if (!$f) {
                $this->log('Error creating the file ' . $this->props["path"]);
                throw new Exception('Error creating the file ' . $this->props["path"]);
            } else {
                if (flock($f, LOCK_EX)) {
                    $header = [0, [$this->props["indexType"]]];
                    $string = self::arrayToString($header, self::$sep);
                    fputs($f, "$string\r\n");
                    fflush($f);
                    flock($f, LOCK_UN);
                }
            }
            fclose($f);


            //Добавляем информацию о базе
            //$cols_title = [0,[$this->dbType]];
            //$this->AddItemToBase ($cols_title);

        }
    }






    public function all(array $filters = [], array $sort = []): array
    {
        //$sort = [$key => $type]

        $cureItems = $this->items;

        if (count($filters) > 0) {
            $cureItems = (new FilterHelpers())->filterItems ($cureItems, $filters);
        }

        /*
        if (count($sort) > 0) {
            foreach ($sort as $sortParam) {

                $keys = $sortParam[0];
                $type = $sortParam[1];
                
                uasort($cureItems, function ($a, $b) use ($keys, $type, $cureItems) {                    
                    $first = $this->getValueByKeys($a, $keys);
                    $second = $this->getValueByKeys($b, $keys);

                    if (!$first || !isset($second)) {
                        return false;
                    } else if ($type == 0) {
                        return $first <=> $second;
                    } else {
                        return $second <=> $first;
                    }
                });
            }
            
        }
        */
        
        return $cureItems;
    }



    public function flt(array $params, array $sort = []): array
    {
        $cureItems = $this->items;
        if (count($params) > 0) {
            foreach ($params as $filt_info) {
                $type = $filt_info[0];
                $key = $filt_info[1];
                $needle = $filt_info[2];

                $cureItems = array_filter($cureItems, function ($item, $id) use ($type, $key, $needle) {
                    if ($type == 'inlist') {
                        if (!isset($item[$key])) return false;
                        return in_array($item[$key], $needle);
                    } else {
                        return false;
                    }
                }, ARRAY_FILTER_USE_BOTH);
            }
        }

        return $cureItems;
    }

    public function add(array $item, $id='')
    {
        if ($id != ''){
            if ($this->get($id)) return -1;
        }

        $item[1] = $this->setItemProps([], $this->activeUser);

        $addedItem = $this->fileUpdate($item, $id);
        if ($addedItem) {
            $itemId = $addedItem[0];
            $this->items[$itemId] = $addedItem;
            return $itemId;
        }
        return  false;
    }

    public function addItems(array $items): bool
    {
        $count = $this->props["lastId"];
        foreach ($items as $id => $info){
            $count++;

            if ($this->props["indexType"] == 'guid'){
                $info[0] = $this->guidv4();
            } else if ($this->props["indexType"] == 'str') {
                $info[0] = $this->generateUniqueString($this->stringLength); // Генерация строкового идентификатора
                while (isset($this->items[$info[0]])) {
                    $info[0] = $this->generateUniqueString($this->stringLength); // Проверка на уникальность
                }
            } else {
                $info[0] = $count;
            }

            $info[1] = $this->setItemProps([], $this->activeUser);
            $this->items[$info[0]] = $info;
        }
        $this->fileSave();
        
        
        return true;
    }

    public function get($id)
    {
        if (!$id) return null;

        if (isset($this->items[$id])) {
            return $this->items[$id];
        }
        return null;
    }

    public function getValues($id, array $keys):  ?array
    {
        
        if (isset($this->items[$id])) {
            $result = [];
            $info = $this->items[$id];
            foreach ($keys as $key_arr){
                if (!is_array($key_arr)) $key_arr = [$key_arr];
                $result[] = $this->getValueByKeys($info, $key_arr);
            }
        }
        return $result ?? null;
    }

    public function upd($id, array $item): bool
    {

        $cureItem = $this->get($id);
        if ($cureItem) {
            $item[0] = $id;
            if (!is_array($cureItem[1])) $cureItem[1] = [];
            $item[1] = $this->setItemProps($cureItem[1], $this->activeUser);

            $itemToUpd = array_replace($cureItem, $item);
            $this->items[$id] = $itemToUpd;
            $this->fileSave();
            return true;
        }
        return false;
    }

    public function updItems(array $items): bool
    {        
        foreach ($items as $id => $info){
            $cureItem = $this->get($id);
            if (!$cureItem) continue;
            $cureItem[1] = $this->setItemProps($cureItem[1], $this->activeUser);
            $itemToUpd = array_replace($cureItem, $info);
            $this->items[$id] = $itemToUpd;            
        }        
        $this->fileSave();        
        
        return true;
    }
    
    public function rpl($id, array $item): bool
    {
        $cureItem = $this->get($id);
        if ($cureItem) {
            $item[0] = $id;
            if (!is_array($cureItem[1])) $cureItem[1] = [];
            $item[1] = $this->setItemProps($cureItem[1], $this->activeUser);

            $itemToUpd = $item;
            $this->items[$id] = $itemToUpd;
            $this->fileSave();
            return true;
        }
        return false;
    }

    public function del($id): bool
    {
        $cureItem = $this->get($id);
        if ($cureItem) {
            unset($this->items[$id]);
            $this->setLastId($this->items);
            $this->fileSave();
            return true;
        }
        return false;
    }

    public function delItems(array $keys): bool
    {
        foreach ($keys as $id){
            $cureItem = $this->get($id);
            if (!$cureItem) continue;
            unset($this->items[$id]);
        }
        $this->fileSave();
        
        
        return true;
    }





    protected function setItemProps(array $props, $userId)
    {
        $cureCreateTime = (isset($props[0]) and $props[0] != '') ? $props[0] : time();
        $cureCreateUser = (isset($props[1]) and $props[1] != '') ? $props[1] : $userId;
        $cureModifyTime = (isset($props[0]) and $props[0] != '') ? time() : '';
        $cureModifyUser = (isset($props[1]) and $props[1] != '') ? $userId : '';
        $props = [
            0 => $cureCreateTime,
            1 => $cureCreateUser,
            2 => $cureModifyTime,
            3 => $cureModifyUser,
        ];
        return $props;
    }


    protected function setLastId(array $array)
    {
        if ($this->props["indexType"] === "num") {
            $keys = array_keys($array);
            sort($keys);
            $this->props["lastId"] = end($keys);
        } else if ($this->props["indexType"] === "str") {
            // Обработка строкового идентификатора
            $keys = array_keys($array);
            $this->props["lastId"] = end($keys);
        }
    }

    protected function fileRead(bool $isIndexed = true): void
    {
        $result = [];
        try {
            $fileHandle = fopen($this->props["path"], 'r');
            if (!$fileHandle) {
                throw new Exception('Error opening the file ' . $this->props["path"]);
            }

            while (($line = fgets($fileHandle)) !== false) {
                $item = self::stringToArray($line, self::$sep);
                if (count($item) == 0) continue;
                if ($isIndexed) {
                    $result[$item[0]] = $item;
                } else {
                    $result[] = $item;
                }
            }

            fclose($fileHandle);

            if ($this->props["indexType"] === "num") {
                $this->setLastId($result);
            } else if ($this->props["indexType"] === "str") {
                // Обработка строкового идентификатора
                $keys = array_keys($result);
                $this->props["lastId"] = end($keys);
            }
            if (isset($result[0])) {
                $this->head = $result[0];
                unset($result[0]);
            }
            $this->items = $result;
        } catch (Exception $e) {
            $this->log('Error reading file ' . $this->props["path"] . ': ' . $e->getMessage());
            throw $e;
        }
    }


    protected function fileUpdate(array $item, $id='')
    {
        if ($id != '') {
            $item[0] = $id;
        } else {
            if ($this->props["indexType"] === "guid") {
                $item[0] = $this->guidv4();
            } else if ($this->props["indexType"] === "str") {
                $item[0] = $this->generateUniqueString($this->stringLength);
                // Проверка на уникальность
                while (isset($this->items[$item[0]])) {
                    $item[0] = $this->generateUniqueString($this->stringLength); 
                }
            } else {
                $item[0] = $this->props["lastId"] + 1;
                $this->props["lastId"]++;
            }
        }

        $string = self::arrayToString($item, self::$sep);

        try {
            $file = fopen($this->props["path"], 'a');
            if (!$file) {
                throw new Exception('Error opening the file ' . $this->props["path"]);
            }

            if (flock($file, LOCK_EX)) {
                fputs($file, "$string\r\n");
                fflush($file);
                flock($file, LOCK_UN);
            }
            fclose($file);

            $this->log('Successfully updated file ' . $this->props["path"]);
        } catch (Exception $e) {
            $this->log('Error updating file ' . $this->props["path"] . ': ' . $e->getMessage());
            throw $e;
        }

        return $item;
    }

    protected function fileSave()
    {
        $data = $this->items;
        if ($this->head) array_unshift($data, $this->head);
        try {
            $file = fopen($this->props["path"], 'w');
            if (flock($file, LOCK_EX)) {
                foreach ($data as $item) {
                    $string = self::arrayToString($item, self::$sep);
                    fputs($file, "$string\r\n");
                }
                fflush($file);
                flock($file, LOCK_UN);
            }
            fclose($file);
        } catch (Exception $e) {
            $this->log('Error saving file ' . $this->props["path"] . ': ' . $e->getMessage());
            throw $e;
        }
        return true;
    }    

    protected function log($message)
    {
        $logFile = $this->props["root"] . 'log.txt';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    protected static function arrayToString(array $array, array $separators, $delBreaks = true): string
    {
        $string = '';

        if (count($array) == 0) return $string;

        $recurse = function ($items, $curLev) use ($separators, $delBreaks, &$recurse) {
            if (count($items) == 0) return '';

            $last_id = max(array_keys($items));

            $elem = [];
            for ($id = 0; $id <= $last_id; $id++) {
                if (!isset($items[$id])) {
                    $elem[$id] = '';
                } else if (is_array($items[$id])) {
                    $nextLev = $curLev + 1;
                    $elem[$id] = $recurse($items[$id], $nextLev);
                } else {
                    if ($delBreaks) $items[$id] = preg_replace("/\n|\r\n/", '<sb>', $items[$id]);
                    $elem[$id] = $items[$id];
                }
            }

            $elem[] = '';
            $separ = $separators[$curLev];
            return implode($separ, $elem);
        };

        $string = $recurse($array, 0);

        return $string;
    }

    protected static function stringToArray(string $string, array $separators): array
    {
        if (strlen($string) == 0 || trim($string) === '') return [];

        $recurs = function ($string, $curLev) use ($separators, &$recurs) {
            $separ = $separators[$curLev];
            $nextLev =  $curLev + 1;

            $items = explode($separ, $string);
            if (count($items) == 0) {
                return [$string];
            }

            if (end($items) == null || trim(end($items)) === '') array_pop($items);

            foreach ($items as $elem) {
                if (isset($separators[$nextLev]) and strpos($elem, $separators[$nextLev]) !== false) {
                    $elem = $recurs($elem, $nextLev);
                } else {
                    $elem = preg_replace("/<sb>/", "\n", $elem);
                }

                $array[] = $elem;
            }

            return $array;
        };

        return $recurs($string, 0);
    }


    /* Генерация id */

    protected function guidv4($data = null): string
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    protected function generateUniqueString(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    
}
