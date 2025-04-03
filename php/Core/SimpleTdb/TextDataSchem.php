<?php

namespace SimpleTdb;

use SimpleTdb\TextDataBase as TDB;
use SimpleTdb\TextDataSchemForm as SchemForm;

use Exception;

/*


Schem fields
0 => 'id',
1 => 'sys',
2 => 'tag (fieldName)',
3 => 'increment',
4 => 'isSystem',
5 => 'dataUnic',
6 => 'dataType',
7 => 'dataSubType',
8 => 'fieldType',
9 => 'fieldViewType',
10 => 'fieldLabel',
11 => 'fieldPlaceholder',
12 => 'fieldDescription',
13 => 'fieldOrder',
14 => 'fieldDefaultVal',
15 => 'fieldProps',
16 => 'linkProps',
17 => 'listItems',


dataSubType [numb] = [
    0 => 'int',
    1 => 'float',
    2 => 'fileSize',

]
*/

class TextDataSchem
{
    protected $modelName;
    protected $modelSchem;
    protected $modelUrl;
    protected $lastInc = 0;

    protected $data;
    public $models;

    protected $modelsSelect;
    protected $modelsSchemSelect;

    
    protected $post;

    protected array $dataSysTypes = [
        1 => ['Обычный'],
        2 => ['Системный'],
    ];

    protected array $dataUnic = [
        1 => ['Должен быть уникальным'],
    ];

    protected array $mainTypes = [
        "numb" => ['Число'],
        "text" => ['Текст'],
        "bool" => ['Чекбокс'],
        "time" => ['Время'],
        "arra" => ['Массив'],
        "list" => ['Список'],
        "link" => ['Связь'],
        "file" => ['Файл'],
        "grup" => ['Группа'],
    ];

    protected array $subTypes = [
        "numb" => [
            0 => 'int',
            1 => 'float',
            2 => 'fileSize',
        ],
    ];

    protected array $fieldTypes = [
        1 => ["Single line Text", "input", "text", "text", "paragraph"],        
        2 => ["Number", "input", "number", "numb", "calculator"],
        3 => ["Password", "input", "password", "text", "asterisk"],
        4 => ["Long Text", "textarea", "", "text", "align justify"],
        5 => ["Long Text Formate", "textarea", "wisiwig", "text", "file alternate outline"],
        6 => ["Checkbox", "input", "checkbox", "bool", "square outline"],
        7 => ["Toggle", "input", "togle", "bool", "toggle on"],
        8 => ["Date", "time", "date", "time", "calendar alternate outline"],
        9 => ["Time", "time", "time", "time", "clock outline"],
        10 => ["Date and Time", "time", "datetime", "time", "calendar plus outline"],
        11 => ["Duration", "time", "duration", "time", "hourglass outline"],
        12 => ["Calendar", "time", "calendar", "time", ""],
        11 => ["List Radio", "list", "radio", "arra", "check circle outline"],
        12 => ["List Checkbox", "list", "checkbox", "arra", "check square outline"],
        13 => ["Select", "select", "", "arra", "list ul"],
        14 => ["Multiple Select", "select", "multiple", "arra", "tasks"],
        15 => ["Group", "group", "", "grup", "layer group"],
        16 => ["List text", "list", "text", "arra", "layer group"],
        17 => ["Link", "link", "", "link", ""], //
        18 => ["scale", "scale", "", "numb", "ruler horizontal"],
        19 => ["File", "file", "", "file", "paperclip"],
        20 => ["Image", "file", "image", "file", "file image outline"],
        21 => ["List text", "list", "text", "arra", ""], //
        22 => ["List textarea", "list", "textarea", "arra", ""],
    ];

    protected array $fieldViewTypes = [
        1 => ["disabled"],
        2 => ["hidden"],
        3 => ["readonly"],
        4 => ["normal"],
        5 => ["required"],
    ];

    public array $linkTypes = ['link', 'file'];
    public array $linkedFields = [];

    public $arrayTypes = ['arra', 'file', 'link', 'list'];

    public function __construct($modelName, $modelPath, $modelSchem)
    {
        $this->modelName = $modelName;
        $this->modelSchem = $modelSchem;        
        $this->modelUrl = '/bases/'.$this->modelName.'/';

        $schemDbName = $this->modelName.'_schem';
        $this->data = TDB::getInstance($schemDbName, $modelPath);        
        
        //Обновляем информацию о схеме из модели в БД
        if (count ($this->modelSchem) > 0){
            foreach ($this->modelSchem as $id => $info){
                if ($this->data->get($id)){
                    //$this->data->upd($id, $info);
                } else {
                    $this->data->add($info, $id);
                }                
            }
        }

        //Получаем последний инкремент
        $this->lastInc = $this->getIncrement ();

        //Получаем все связи
        foreach ($this->data->all() as $id => $info){
            if (in_array($info[6], $this->linkTypes)){
                $this->linkedFields[$id] = $info;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["form_info"])){
            $this->post = $_POST["form_info"];
        }
        
    }

    public function setModels ($models)
    {
        $this->models = $models;
    }

    public function getModels ()
    {
        return $this->models;
    }    


    public function getSchem ($viewType='')
    {
        $result =  $this->data->all();

        if ($viewType == 'form') {
            foreach ($result as $sId => $sInfo){
                $schemType = $sInfo[6];
                if ($schemType == 'link' or $schemType == 'file'){
                    
                    $result[$sId][17] = $this->getVariantsLinks ($sInfo);
                }
            }
        }

        return $result;
    }

    public function getlinkedFields ()
    {
        return $this->linkedFields;
    }


    protected function getVariantsLinks ($sInfo)
    {
        //Пропускаем, если в схеме не задана связь
        if (!isset($sInfo[16]) or !is_array($sInfo[16])){
            return []; 
        }

        //Пропускаем, если в схеме не задана модель
        if (!isset($sInfo[16][0]) or $sInfo[16][0] == ''){
            return []; 
        }

        $result = [];        

        $cureModelName = $sInfo[16][0];
        $cureModel = $this->models[$cureModelName];
        $cureModelSchem = $cureModel->schem->getSchem();
        $cureModelCols = $this->getSchemCols ($sInfo, $cureModelSchem);
        
        foreach ($cureModel->all() as $id => $info) {
            $textArr = [];
            foreach ($cureModelCols as $cId){
                if (is_array($info[$cId])) continue;
                $textArr[] = $info[$cId];
            }

            $result[$id][0] = implode(" ", $textArr);
            if ($sInfo[6] == 'file'){
                $result[$id][1] = $info[2];
            }
            //echo $id.' '. implode(" ", $textArr) .' <br>';
        }

        return $result;
    }

    protected function getSchemCols ($sInfo, $cureModelSchem)
    {
        $colls = [];
        $cureModelFields = $sInfo[16][1] ?? [3];
        if (!is_array($cureModelFields)) $cureModelFields = [$cureModelFields];
        //print_r($cureModelFields);
        
        foreach ($cureModelFields as $fId){
            $fieldCols = $cureModelSchem[$fId][3] ?? 3;
            $colls[] = $fieldCols;
        }

        return $colls;
    }

    protected function getIncrement ()
    {
        $lastInc = 0;
        foreach ($this->data->all() as $itemInfo){
            $itemInc = $itemInfo[3];
            $lastInc = ($itemInc > $lastInc)? $itemInc : $lastInc;
        }
        return $lastInc;
    }










    /*
    Преобразование значений для отображения
    */

    public function prepareTableData ($data)
    {
        $cols = $rows = [];
        $arrayTypes = ['arra', 'file', 'link', 'list'];

        foreach ($this->data->all() as $sId => $sInfo){
            //Пропускаем системные поля
            if ($sInfo[4] == 2) continue;

            $colId = $sInfo[3];
            $type = $sInfo[6];
            
            $colName  = $sInfo[10] ?? $sInfo[2];
            $cols[$colId] = [$colName];

            $colSchem [$colId] = $sInfo;

            if ($type == 'link') {
                $colSchem[$colId][17] = $this->getVariantsLinks ($sInfo);
            }
        }

        foreach ($data as $dId => $dInfo){

            foreach ($colSchem as $colId => $schemInfo){

                //echo $colId.' ';
                //print_r($dInfo[$colId]);
                //echo '<br>';

                $value = $dInfo[$colId] ?? '';
                $type = $schemInfo[6];
                $funcName = 'prepView'. ucfirst($type);

                //Проверяем значения - массивы
                if (in_array($type, $arrayTypes)){
                    if (!is_array($value)) $value = [$value];
                    $value = array_diff($value, [""]);
                    if (count($value) == 0) $value = '';
                }

                if (method_exists($this, $funcName) and $value != ''){
                    [$text, $title, $count] = $this->$funcName($value, $schemInfo);
                } else {
                    [$text, $title, $count] = $this->prepViewDef($value, $schemInfo);
                }                

                $textWrap = '<div class="cell-wrap"><div class="cell-text" title="'.$title.'">'.$text.'</div></div>';
                if ($count > 1) $textWrap .= '<div class="cell-count">'.$count.'</div>';

                $row[$colId] = [$textWrap];
            }

            $rows[$dId] = $row;
        }

        return [$cols, $rows];
    }

    public function prepInfoSchem ($info, $prepType='view')
    {
        $modifyInfo = [];
        foreach ($this->data->all() as $schemId => $schemInfo){
            $colId = $schemInfo[3];
            if ($colId === '') continue;

            
            if ($prepType == 'form'){
                if (!isset($info[$colId])) $info[$colId] = '';
            }
            

            $type = $schemInfo[6];
            $value = $info[$colId] ?? '';

            

            if (in_array($type, $this->arrayTypes)){
                $value = $this->convertToArray($value);                
            }
            
            $funcName = 'prepView'. ucfirst($type);
            if (!method_exists($this, $funcName)) $funcName = 'prepViewDef';
            [$text, $title, $count] = $this->$funcName($value, $schemInfo, $prepType);

            if ($prepType == 'form' or $prepType == 'post'){
                $modifyInfo[$colId] = $text;
            }

            if ($prepType == 'view'){
                $modifyInfo[$colId] = $text;
            }
        }
        return $modifyInfo;

        /*
        foreach ($colSchem as $colId => $schemInfo){

            //echo $colId.' ';
            //print_r($dInfo[$colId]);
            //echo '<br>';

            $value = $dInfo[$colId] ?? '';
            $type = $schemInfo[6];
            $funcName = 'prepView'. ucfirst($type);

            //Проверяем значения - массивы
            if (in_array($type, $arrayTypes)){
                if (!is_array($value)) $value = [$value];
                $value = array_diff($value, [""]);
                if (count($value) == 0) $value = '';
            }

            if (method_exists($this, $funcName) and $value != ''){
                [$text, $title, $count] = $this->$funcName($value, $schemInfo);
            } else {
                [$text, $title, $count] = $this->prepViewDef($value, $schemInfo);
            }

            $textWrap = '<div class="cell-wrap"><div class="cell-text" title="'.$title.'">'.$text.'</div></div>';
            if ($count > 1) $textWrap .= '<div class="cell-count">'.$count.'</div>';

            $row[$colId] = [$textWrap];
        }
        */
    }

    public function prepViewDef ($value, $schem, $prepType='view')
    {
        if ($value == ''){
            return ['','', 0];
        }
        
        $text = $value;
        $count = 1;
        if (is_array($value)) {
            $text = implode(", ", $value);
            $count = count($value);
        }
        return [$text, $text, $count];
    }

    public function prepViewNumb ($value, $schem, $prepType='view')
    {
        //echo $schem[2]. ' '.$value.'<br>';        
        $subType = $schem[7];
        
        if (!is_numeric($value)) {
            return ['','', 0];
        }

        $textValue = $value;
        if ($subType == 2){
            $textValue = $this->formatBytes($value);
        }
        return [$textValue, $textValue, 1];
    }

    public function prepViewTime ($value, $schem, $prepType='view')
    {
        //echo $schem[2]. ' '.$value.'<br>';        
        $subType = $schem[7];
        $fieldType = $schem[8];
        
        if (!is_numeric($value)){
            return ['','', 0];
        }
        
        $text = date('d.m.Y', $value);
        if ($fieldType == 10){
            $text = date('d.m.Y H:i:s', $value);
        }
        return [$text, $text, 1];
    }

    public function prepViewArra ($value, $schem, $prepType='view')
    {
        if ($prepType == 'form' or $prepType == 'post'){
            return [$value, '', count($value)];
        }

        $title = implode(', ', $value);
        $text = implode('</span><span class="ui label">', $value);
        $text = '<span class="ui label">'.$text.'</span>';

        return [$text, $title, count($value)];
    }

    public function prepViewFile ($value, $schem, $prepType='view')
    {
        if ($prepType == 'form' or $prepType == 'post'){
            return [$value, '', count($value)];
        }

        $title = implode(', ', $value);
        $text = implode('</span><span class="ui label">', $value);
        $text = '<span class="ui label">'.$text.'</span>';

        return [$text, $title, 1];
    }

    public function prepViewLink ($value, $schem, $prepType='view')
    {
        if ($prepType == 'form' or $prepType == 'post'){
            return [$value, '', count($value)];
        }
        
        $linkedList = $schem[17];
        $linkedNames = [];
        foreach ($value as $linkedId){
            $linkedNames[] = $linkedList[$linkedId][0] ?? '';
        }

        $title = implode(', ', $linkedNames);
        $text  = implode('</span><span class="ui label">', $linkedNames);
        $text  = '<span class="ui label">'.$text.'</span>';

        return [$text, $title, count($linkedNames)];
    }


    public function prepViewList ($value, $schem, $prepType='view')
    {
        if ($prepType == 'form' or $prepType == 'post'){
            return [$value, '', count($value)];
        }
        
        $itemsList = $schem[17];
        $itemsNames = [];
        foreach ($value as $linkedId){
            $linkedNames[] = $itemsList[$linkedId][0];
        }

        $title = implode(', ', $linkedNames);
        $text  = implode('</span><span class="ui label">', $linkedNames);
        $text  = '<span class="ui label">'.$text.'</span>';

        return [$text, $title, count($linkedNames)];
    }    

    /*
    Отображение формы
    public function viewScheme ($html = '')
    {
        
        $cureStage = $this->post['stage'] ?? 1;

        if ($cureStage == 2){
            $html .= $this->saveSchemToBase();
        }

        $html .= $this->viewSchemForm ();
        return $html;
    }

    public function viewSchemForm () 
    {        
        $form = new SchemForm($this, $this->modelName, $this->modelUrl);
        return $form->viewForm();
    }
    */





    /*
    Обновление схемы
    */

    



    protected function saveSchemToBase ()
    {
                
        $tobase = [];
        $keysCollected = [
            'skip' => [],
            'del' => [],
            'upd' => [],
            'add' => [],
        ];
        $schemKeys = array_keys($this->data->all());
        

        foreach ($this->post as $id => $info){
            if (!is_numeric($id) ) continue;

            $info = $this->checkSchemItems ($info);
            $isSystem = ($info[4] == 2) ? true : false;
            
            
            if (in_array($id, $schemKeys) and $this->data->get($id)){

                $current = $this->checkSchemItems ($this->data->get($id));

                $equal = $this->isEqual($current, $info);

                if (!$equal){                    
                    $tobase[$id] = array_replace($this->data->get($id), $info);
                    $keysCollected['upd'][] = $id;
                } else {
                    $keysCollected['skip'][] = $id;
                }
                
            } else {
                $tobase[$id] = $info;
                //$keysToAdd[] = $id;
                $keysCollected['add'][] = $id;
            }            
        }
        
        $currentKeys = array_merge($keysCollected['skip'], $keysCollected['upd']);
        $keysCollected['del'] = array_diff($schemKeys, $currentKeys);
        
        
        foreach ($keysCollected as $type => $keys){
            if ($type == 'skip') continue;
            $funcName = $type.'Items';
            $this->$funcName($keys, $tobase);
        }
        
        $html = '
        <div class="ui positive message">
        Изначально: '.count($schemKeys).' ('.implode (", ", $schemKeys).')<br>
        Без изменений: '.count($keysCollected['skip']).' ('.implode (", ", $keysCollected['skip']).')<br>
        Удалено: '.count($keysCollected['del']).' ('.implode (", ", $keysCollected['del']).')<br>
        Обновлено: '.count($keysCollected['upd']).' ('.implode (", ", $keysCollected['upd']).')<br>
        Добавлено: '.count($keysCollected['add']).' ('.implode (", ", $keysCollected['add']).')
        </div>
        ';

        return $html;        

    }



    protected function delItems ($keys, $tobase)
    {
        foreach ($keys as $id){
            $cureItem = $this->data->get($id);
            $cureType = $cureItem[6];

            //Добавить проверку на содержание данных в столбце

            if ($this->data->get($id)){
                $this->data->del($id);
            }

            if ($cureType == 'link'){
                $this->delLink ($cureItem);
            }

        }
        
        $this->lastInc = $this->getIncrement ();
    }

    protected function updItems ($keys, $tobase)
    {
        foreach ($keys as $id){
            $cureItem = $this->data->get($id);
            $cureType = $cureItem[6];
            $newType = $tobase[$id][6];
            
            //Добавляем ссылку и получаем ID поля в базе
            if ($newType == 'link' or $newType == 'file'){

                if ($newType == 'file'){
                    $tobase[$id][16][0] = 'uploads';
                    $tobase[$id][16][1] = 4;
                    $newType = 'link'; // тип добавляемой в uploads колонки
                }   

                $tobase[$id][16][2] = $this->addLink ($id, $tobase[$id], $newType);
            }
            /*
            if ($cureType == 'link' or $newType == 'link' or $newType == 'file'){
                $linkProps = $tobase[$id][16];
                if ($newType == 'file'){
                    $newType = 'link'; // тип добавляемой в uploads колонки
                }
                

                if ($cureType != $newType){
                    if ($cureType == 'link'){
                        $this->delLink ($tobase[$id]);
                    } else {
                        $tobase[$id][16][2] = $this->addLink ($id, $tobase[$id], $newType);
                    }

                } else if (!isset($linkProps[2]) or $linkProps[2] == '') {
                    $newType = 'link';
                    $tobase[$id][16][2] = $this->addLink ($id, $tobase[$id], $newType);
                }
            }
            */

            $this->data->upd($id, $tobase[$id]);
        }
    }

    protected function addItems ($keys, $tobase)
    {
        foreach ($keys as $id){
            $this->lastInc ++;
            $tobase[$id][3] = $this->lastInc;
            
            $newType = $tobase[$id][6];

            if ($newType == 'file'){
                $tobase[$id][16][0] = 'uploads';
                $tobase[$id][16][1] = 4;
                $newType = 'link'; // тип добавляемой в uploads колонки
            }            
            
            if ($newType == 'link'){                
                $tobase[$id][16][2] = $this->addLink ($id, $tobase[$id], $newType);
            }

            $newId = $this->data->add($tobase[$id], $id);
        }
    }

    



    /*
    Добавление связей
    */
    


    protected function addLink ($id, $item, $newType)
    {
        if (!isset($item[16][0]) or $item[16][0] == ''){
            $_SESSION["message"][] = ['war', 'Связь не установлена. Не указана база, с которой нужно установить связь поля ['.$id.'].'];
            return '';
        }

        $targetBaseName = $item[16][0];        
        $targetModel = $this->models[$targetBaseName];
        $targetId = $this->checkLink ($targetModel, $this->modelName, $id);

        if ($targetId){
            $_SESSION["message"][] = ['war', 'Связь уже установлена. ID = '.$targetId];
            return $targetId;
        }

        $itemName = $this->modelName.'_'. uniqid();
        $newField [2] = $itemName; //Тэг элемента
        $newField [6] = $newType; //Тип данных
        $newField [8] = 14; //Тип поля - Multiple Select
        $newField[10] = $this->modelName; //Имя поля
        $newField [16][0] = $this->modelName; //текущая база
        $newField [16][1] = 3;  //Поле по умолчанию
        $newField [16][2] = $id;  //id текущего поля

        $targetId = $targetModel->schem->addCol ($itemName, $newField);        

        //$item[16][2] = $targetId;
        //$this->data->upd($id, $item);

        return $targetId;
    }

    protected function checkLink ($targetModel, $baseName, $id)
    {
        foreach ($targetModel->schem->getschem () as $sId => $sInfo){
            if ($sInfo[6] != 'link') continue;
            if (!isset($sInfo[16]) or !is_array($sInfo[16])) continue;

            //Если уже есть связь с текущей базой и текущим id
            $cureBaseName = $sInfo[16][0] ?? '';
            $cureBaseId = $sInfo[16][2] ?? '';
            if ($cureBaseName == $baseName and  $cureBaseId == $id){
                return $sId;
            }
        }
        return false;
    }


    protected function delLink ($item)
    {
        $targetBase = $item[16][0];
        $targetId = $item[16][2] ?? null;

        if (!$targetId){
            $_SESSION["message"][] = ['war', 'Связь не удалена. Не указана колонка в базае, которую нужно удалить.'];
            return false;
        }       
        if (!isset($this->models[$targetBase])){
            return false;
        }
        $this->models[$targetBase]->schem->delCol ($targetId);
        
        return true;
    }


    public function addCol ($name, $info=[])
    {
        //Проверка на существование поля с таким именем
        foreach ($this->data->all() as $colId => $colInfo){            
            if ( $colInfo[2] == $name) {
                $_SESSION["message"][] = ['war', 'Колонка с именем ['.$name.'] уже существует.'];
                return [$colId, $colInfo[3]];
            }
        }

        $mainType = $info[6] ?? 'text';
        $fieldType = $info[8] ?? 1;
        $fieldTitle = $info[10] ?? $name;
        $linkProps = $info[16] ?? [];

        
        $this->lastInc ++;
        $tobase = [
            0 => "", //id
            1 => [], //system
            2 => $name, //tag
            3 => $this->lastInc, //increment
            4 => 1,
            5 => false, //unic
            6 =>  $mainType,  //main type
            7 => '',   //sub type
            8 => $fieldType, //field type: text
            9 => 4, //field display: normal
            10 => $fieldTitle, //field title
            11 => '',
            12 => '',
            13 => '', //order
            14 => '',
            15 => [],
            16 => $linkProps,
            17 => []
        ];
        $colId = $this->data->add($tobase);
        //return [$colId, $this->lastInc];

        return $colId;
    }

    public function delCol ($id)
    {
        return $this->data->del($id);

        //Добавить удаление данных для колонки из модели

    }




    
    

    /*
    Вспомогательные функции
    */


    protected function checkSchemItems ($info)
    {
        $info[10] = (!isset($info[10]) or $info[10] == '')? $info[2] :  $info[10];
        $info[15] = $info[15] ?? '';
        $info[16] = $info[16] ?? '';
        $info[17] = $info[17] ?? '';

        $info[15] = $this->convertToArray($info[15]);
        $info[16] = $this->convertToArray($info[16]);
        $info[17] = $this->convertToArray($info[17]);

        $schemItem = $info;
        ksort($schemItem);
        
        return $schemItem;
    }


    protected function isEqual ($old, $new)
    {
        //if (isset($old[0])) unset($old[0]);
        if (isset($old[1])) unset($old[1]);

        //Проверяем установку связи
        if (in_array($new[6], $this->linkTypes)){
            $new[16][0] = $new[16][0] ?? '';
            $new[16][2] = $new[16][2] ?? '';
            if ($new[16][0] === '' or $new[16][2] === ''){
                return false;
            }
        }

        $jsonOld = json_encode($old, JSON_UNESCAPED_UNICODE);
        $jsonNew = json_encode($new, JSON_UNESCAPED_UNICODE);
        //echo $jsonOld.'<br>'.$jsonNew.'<hr>';

        if ($jsonOld === $jsonNew) {
            return true;
        }

        return false;
    }



    public function convertToArray($value)
    {
        if (!is_array($value)){
            $value = ($value == '')? [] : [$value];
        }
        $value = array_filter($value, fn($item) => ($item == '')? false : true);

        return $value;
    }


    protected function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Calculate the value in the appropriate unit
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
