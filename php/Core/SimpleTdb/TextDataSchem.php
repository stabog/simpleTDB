<?php

namespace SimpleTdb;

use SimpleTdb\TextDataBase as TDB;
use SimpleTdb\TextDataModel as TDM;
use SimpleTdb\TextDataModelException;

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
    protected $modelPath;
    protected $modelSchem;
    protected $lastInc = 0;

    protected $data;
    protected $models;
    

    /*
    tag => [
        tag
        ausData => [add_time, add_user, upd_time, upd_user, snc_time, snc_user]
        inc: numb
        title: text
        isSystem: bool
        isUnic: bool        
        dataType: text ?? one of mainTypes
        dataSubType: text ?? one of subTypes
        parentId: text 
        items: dict
        fieldInfo => [
            fieldTypeId: numb  <- зависит от dataType
            fieldViewTypeId: numb <- зависит от fieldType
            fieldPlaceholder: text 
            fieldDescription: text
            fieldOrder: numb
            fieldDefaultVal <- зависит от dataType
            fieldProps: list
        ]
        linkProps: list
       

    ]
    
    protected $schemItems = [
        1 => [1, '', 'id', 0, 2, true, 'text', '', 1, 2, 'Id', '', '', 1, '', [], [], []],
        2 => [2, '', 'sys', 1, 2, false, 'arra', '', 21, 2, 'Системные поля', '', '', 2, '', [], [], []],
        3 => [3, '', 'tag', 2, 2, true, 'text', '', 1, 2, 'tag', '', '', 1, '', [], [], []],
        4 => [4, '', 'increment', 3, 2, true, 'text', '', 1, 2, 'increment', '', '', 1, '', [], [], []],
        5 => [5, '', 'isSystem', 4, 2, false, 'text', '', 1, 2, 'isSystem', '', '', 1, '', [], [], []],
        6 => [6, '', 'dataUnic', 5, 2, false, 'text', '', 1, 2, 'dataUnic', '', '', 1, '', [], [], []],
        7 => [7, '', 'dataType', 6, 2, false, 'text', '', 1, 2, 'dataType', '', '', 1, '', [], [], []],
        8 => [8, '', 'dataSubType', 7, 2, false, 'text', '', 1, 2, 'dataSubType', '', '', 1, '', [], [], []],
        9 => [9, '', 'fieldType', 8, 2, false, 'text', '', 1, 2, 'field Type', '', '', 1, '', [], [], []],
        10 => [10, '', 'fieldViewType', 9, 2, false, 'text', '', 1, 2, 'field ViewType', '', '', 1, '', [], [], []],
        11 => [11, '', 'fieldLabel', 10, 2, false, 'text', '', 1, 2, 'field Label', '', '', 1, '', [], [], []],
        12 => [12, '', 'fieldPlaceholder', 11, 2, false, 'text', '', 1, 2, 'field Placeholder', '', '', 1, '', [], [], []],
        13 => [13, '', 'fieldDescription', 12, 2, false, 'text', '', 1, 2, 'field Description', '', '', 1, '', [], [], []],
        14 => [14, '', 'fieldOrder', 13, 2, false, 'numb', '', 1, 2, 'field Order', '', '', 1, '', [], [], []],
        15 => [15, '', 'fieldDefaultVal', 14, 2, false, 'text', '', 1, 2, 'field Default Val', '', '', 1, '', [], [], []],
        16 => [16, '', 'fieldProps', 15, 2, false, 'list', '', 1, 2, 'field Props', '', '', 1, '', [], [], []],
        17 => [17, '', 'linkProps', 16, 2, false, 'list', '', 1, 2, 'link Props', '', '', 1, '', [], [], []],
        17 => [18, '', 'listItems', 17, 2, false, 'list', '', 1, 2, 'list Items', '', '', 1, '', [], [], []],
    ]; 
    */


    protected $schemItems = [
        0  => [0, [], 'id', 'Id колонки', false, true, 'text', '', '', [], [], []],
        1  => [1, [], 'ausData', 'Информация о создании/редактировании', true, false, 'list', '', '', [], [], []],
        2  => [2, [], 'tag', 'Тэг колонки', true, false, 'numb', '', '', [], [], []],
        3  => [3, [], 'title', 'Имя колонки', false, false, 'text', '', '', [], [], []],
        4  => [4, [], 'isSystem', 'Колонка является системной', true, false, 'bool', '', '', [], [], []],
        5  => [5, [], 'isUnic', 'Значение должно быть уникальным', false, false, 'bool', '', '', [], [], []],
        6  => [6, [], 'dataType', 'Тип данных', false, false, 'text', '', '', [], [], []],
        7  => [7, [], 'dataSubType', 'Подтип данных', false, false, 'numb', '', '', [], [], []],
        8  => [8, [], 'parentId', 'Id родительского элемента', false, false, 'text', '', '', [], [], []],
        9  => [9, [], 'items', 'Текущие элементы для выбора', false, false, 'list', '', '', [], [], []],
        10 => [10, [], 'fieldInfo', 'Настройки поля для формы', false, false, 'list', '', '', [], [], []],
        11 => [11, [], 'linkProps', 'Настройки для связи', false, false, 'list', '', '', [], [], []],
        
    ]; 

    //4 Системный (не может изменяться пользователем)
    //5 Уникальность ()    

    //6
    protected array $mainTypes = [
        "numb" => ['Число'],
        "text" => ['Текст'],
        "bool" => ['Чекбокс'],
        "time" => ['Время'],       
        "list" => ['Список'],
        "dict" => ['Cловарь'],
        "link" => ['Связь'],
        "file" => ['Файл'],
        "grup" => ['Группа'],
    ];

    //7
    protected array $subTypes = [
        "numb" => [
            0 => 'int',
            1 => 'float',
            2 => 'fileSize',
        ],
    ];

    //8
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

    //9
    protected array $fieldViewTypes = [
        1 => ["disabled"],
        2 => ["hidden"],
        3 => ["readonly"],
        4 => ["normal"],
        5 => ["required"],
    ];



    public array $linkTypes = ['link', 'file'];
    public array $linkedFields = [];

    public $arrayTypes = ['list', 'dict', 'file', 'link'];
    

    public function __construct($modelName, $modelPath, $modelSchem)
    {
        $this->modelName = $modelName;
        $this->modelPath = $modelPath;
        $this->modelSchem = $modelSchem;

        $schemDbName = $this->modelName.'_schem';
        $this->data = TDB::getInstance($schemDbName, $modelPath);
        
        //Обновляем информацию о схеме из модели в БД
        if (count ($this->modelSchem) > 0){
            
            foreach ($this->modelSchem as $id => $info){
                if (!$this->data->get($id)){
                    $this->data->add($info, $id);                    
                }                
            }
        }
        

        /*

        //Получаем последний инкремент
        $this->lastInc = $this->getIncrement ();

        //Получаем все связи
        foreach ($this->data->all() as $id => $info){
            if (in_array($info[6], $this->linkTypes)){
                $this->linkedFields[$id] = $info;
            }
        }
        */
        
    }


    /*
    public function setModels ($models)
    {
        $this->models = $models;
    }

    public function getModels ()
    {
        return $this->models;
    }
    */


    public function getSchem ($newType="data")
    {
        $items =  $this->data->all();
        $schem = $this->schemItems;

        $convertedItems = $this->validateAndConvertItems ($items, $schem, "data", $newType);       

        return $convertedItems;
    }

    

    public function validateAndConvertItems ($items, $schem, $cureType="data", $newType="data")
    {
        $result = [];
        foreach ($items as $itemId => $itemInfo){
            $checkedItem = $this->validateAndConvertItemValues ($itemInfo, $schem, $cureType, $newType);
            //$result[$itemId] = $checkedItem;
            $result[] = $checkedItem;
        }
        return $result;
    }

    public function validateAndConvertItemValues ($itemInfo, $schem, $cureType="data", $newType="data")
    {
        $result = [];
        foreach ($schem as $sId => $sInfo){
            $dataId = $sId;
            $dataTag = $sInfo[2];
            $dataType = $sInfo[6];

            if ($cureType =="data"){
                $cureKey = $dataId;                
            } else if ($cureType =="dict"){
                $cureKey = $dataTag;
            }

            //Пропускаем элементы, которых нет в $itemInfo
            if (!isset($itemInfo[$cureKey])) continue;

            $cureVal  = $itemInfo[$cureKey];
            

            if ($dataType == "bool"){
                $cureVal = ($cureVal != "")? "1" : "";
            }

            if (in_array($dataType, $this->arrayTypes)){
                $cureVal = $this->convertToArray($cureVal);
            }
            
            if ($newType === 'data') $returnKey = $dataId;
            if ($newType === 'dict') $returnKey = $dataTag;
            $result[$returnKey] = $cureVal;
        }
        return $result;
    }
    

    /*

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
            $itemInc = $itemInfo[2];
            $lastInc = ($itemInc > $lastInc)? $itemInc : $lastInc;
        }
        return $lastInc;
    }
    */


    public function addCol ($info, $id, $checkLinks = false)
    {
        /*
        'id' => ['id', [], 0, 'Id колонки', false, true, 'text', '', '', [], [], []],
        'ausData' => ['ausData', [], 1, 'Информация о создании/редактировании', true, false, 'list', '', '', [], [], []],
        'inc' => ['inc', [], 2, 'Инкремент для базы', true, false, 'numb', '', '', [], [], []],
        'title' => ['title', [], 3, 'Имя колонки', false, false, 'text', '', '', [], [], []],
        'isSystem' => ['isSystem', [], 4, 'Колонка является системной', true, false, 'bool', '', '', [], [], []],
        'isUnic' => ['isUnic', [], 5, 'Значение должно быть уникальным', false, false, 'bool', '', '', [], [], []],
        'dataType' => ['dataType', [], 6, 'Тип данных', false, false, 'text', '', '', [], [], []],
        'dataSubType' => ['dataSubType', [], 7, 'Подтип данных', false, false, 'numb', '', '', [], [], []],
        'parentId' => ['parentId', [], 8, 'Id родительского элемента', false, false, 'text', '', '', [], [], []],
        'items' => ['items', [], 9, 'Текущие элементы для выбора', false, false, 'dict', '', '', [], [], []],
        'fieldInfo' => ['fieldInfo', [], 10, 'Настройки поля для формы', false, false, 'list', '', '', [], [], []],
        'linkProps' => ['linkProps', [], 11, 'Настройки для связи', false, false, 'list', '', '', [], [], []],
        */

        $checkedId = $this->convertToValidVariableName($id);
        
        //Проверка на существование поля с таким именем
        if (isset($this->data->all()[$checkedId])){
            if (!$checkLinks){
                $_SESSION["message"][] = ['war', 'Колонка с именем ['.$checkedId.'] уже существует.'];
                return $checkedId;
            }
            $checkedId .= rand(1000, 9999);

        }

        $title        = isset($info[3])  ? $info[3] : $id;
        $isSystem     = isset($info[4])  ? $info[4] : false;
        $isUnic       = isset($info[5])  ? $info[5] : false;
        $dataType     = isset($info[6])  ? $info[6] : 'text';
        $dataSubtype  = isset($info[7])  ? $info[7] : '';        
        $parentId     = isset($info[8])  ? $info[8] : '';
        $items        = isset($info[9])  ? $info[9] : [];
        $fieldInfo    = isset($info[10]) ? $info[10] : [];
        $linkProps    = isset($info[11]) ? $info[11] : [];
        
        $this->lastInc ++;
        $tobase = [
            0 => $checkedId, //id
            1 => [], //system            
            2 => $this->lastInc, //increment
            3 => $title, //Title
            4 => $isSystem, //isSystem
            5 => $isUnic, //isUnic
            6 => $dataType, //dataType
            7 => $dataSubtype,   //dataSubtype
            8 => $parentId,   //parentId
            9 => $items,   //items
            10 => $fieldInfo,   //fieldInfo
            11 => $linkProps, //linkProps
        ];
        $colId = $this->data->add($tobase, $checkedId);
        return $colId;
    }

    public function updCol ($id, $info)
    {
        return $this->data->upd($id, $info);
    }

    public function delCol ($id)
    {
        return $this->data->del($id);
        //Добавить удаление данных для колонки из модели

    }




    
    

    /*
    Вспомогательные функции
    */

    public function convertToArray($value)
    {
        if (!is_array($value)){
            $value = ($value == '')? [] : [$value];
        }
        //$value = array_filter($value, fn($item) => ($item == '')? false : true);

        return $value;
    }


    public function checkItemsBySchem($items, $props)
    {
        $result = [];
        foreach ($items as $itemInfo){
            if (!isset($itemInfo["id"])) continue;

            $itemId = $itemInfo["id"];
            $result[$itemId] = $this->checkValueBySchem($itemInfo, $props);
        }
        return $result;
    }


    // Проверяем значения по схеме, дополняем схему
    //public function checkValueBySchem($info, $surce="user")
    public function checkValueBySchem($info, $props)
    {
        $result = [];

        if (isset($props["isSchem"])){
            $schem = $this->schemItems;            
            $processedInfo = $info;
            
        } else {
            $schem = $this->getSchem();

            //Добавление колонок
            $schemNames = array_column($schem, 0);
            $processedInfo = [];
            foreach ($info as $infoId => $value) {
                $colName = $infoId;
                if (!in_array($infoId, $schemNames) and $value != '') {
                    $colName = $this->addCol([], $infoId);                
                }
                $processedInfo[$colName] = $value;
            }
        }        
        
        

        foreach ($schem as $sId => $sInfo) {
            if ($sInfo[4]) {
                //Пропускаем системную колонку
                if (!isset($props["converAll"])) continue;
            }
            if ($sInfo[5]) {
                //Добавить проверку на уникальность
            }
            $type = $sInfo[6];
            $colName = $sId;
            $colId = $sInfo[2];

            $cureVal = '';
            if (isset($processedInfo[$colName])) {                
                $cureVal = $processedInfo[$colName];
            }
            //if (isset($info[$colId])) $cureVal = $info[$colId];

            // Если bool
            if ($type == 'bool') {
                $cureVal = $cureVal ? "1" : "";
            }

            // Если link, arra, file
            if (in_array($type, $this->arrayTypes)) {
                $cureVal = $this->convertToArray($cureVal);
            }

            //echo '['.$colId.']: '.$cureVal.' \r\n';

            $result[$colId] = $cureVal;
        }

        return $result;
    }



    public function convertListItemsToDict ($items, $props=[])
    {        
        $result = [];
        foreach ($items as $itemId=> $itemInfo) {
            $convertedItem = $this->convertListItemToDict ($itemInfo, $props);
            if (isset($props['idToKeys'])){
                unset($convertedItem["id"]);
                $result[$itemId] = $convertedItem;
            } else {
                $result[] = $convertedItem;
            }
            
        }
        return $result;
    }


    public function convertListItemToDict ($item, $props=[])
    {
        $result = [];
        if (isset($props["isSchem"])){
            $schemItems = $this->schemItems;
        } else {
            $schemItems = $this->getSchem();
        }
        

        foreach ($schemItems as $sId => $sInfo) {
            $type = $sInfo[6];
            $colName = $sId;
            $colId = $sInfo[2];

            if ($sId === 'passHash') {
                if (isset($props["showHash"])) continue;
                //echo $item[$colId];
            }

            $cureVal = '';
            //Получаем инфо по colId
            if (isset($item[$colId])) $cureVal = $item[$colId];

            

            // Если link, arra, file
            if (in_array($type, $this->arrayTypes)) {
                $cureVal = $this->convertToArray($cureVal);                
            }

            //Сохраняем инфо по colName
            $result[$colName] = $cureVal;
        }        

        return $result;
    }

    // Новая функция для преобразования строки в корректное название переменной
    protected function convertToValidVariableName(string $input): string
    {
        // Преобразуем строку в нижний регистр
        $input = strtolower($input);

        // Заменяем все не буквенно-цифровые символы на подчеркивания
        $input = preg_replace('/[^a-z0-9]+/', '_', $input);

        // Удаляем лишние подчеркивания в начале и конце строки
        $input = trim($input, '_');

        // Заменяем несколько подчеркиваний подряд на одно
        $input = preg_replace('/_+/', '_', $input);

        return $input;
    }


    /* Обновление Схемы */

    public function saveSchem ($items)
    {
                
        $toAdd = $toUpd = $toSkip = [];
        

        //$newItems = $items;
        //$oldItems = $toDel = $this->getSchem();

        foreach ($items as $sInfo){
            $inc = $sInfo[2];
            $newItems [$inc] = $sInfo;
        }

        foreach ($this->getSchem() as $sInfo){
            $inc = $sInfo[2];
            $oldItems [$inc] = $sInfo;
        }

        $toDel = $oldItems;        

        foreach ($newItems as $inc => $itemInfo){
            
            if (!isset($oldItems[$inc])){
                $toAdd[$inc] = $itemInfo;
                continue;
            } else {

                if ($this->isEqual($oldItems[$inc], $itemInfo)){
                    $toSkip[$inc] = $itemInfo;
                } else {
                    $toUpd[$inc] = $itemInfo;
                }

                unset($toDel[$inc]);
            }
            
        }  

        /*

        foreach ($newItems as $itemId => $itemInfo){    
            $inc = $sInfo[2];        
            
            if (!isset($oldItemsByInc[$inc])){
                $toAdd[$itemId] = $itemInfo;
                continue;
            } else {

                if ($oldItemsByInc[$inc][0] != )

                if ($this->isEqual($oldItemsByInc[$inc], $itemInfo)){
                    $toSkip[$itemId] = $itemInfo;
                } else {
                    $toUpd[$itemId] = $itemInfo;
                }

                unset($toDel[$itemId]);
            }
            
        }     
        */   

        foreach ($toDel as $inc => $info){
            $id = $info[0];
            if (in_array($info[6], ["link", "file"])){
                $info = $this->checkSchemLink([], $oldItems[$inc], $this->modelName);                
            }
            $this->delCol ($id);
        }

        foreach ($toUpd as $inc => $info){
            $id = $oldItems[$inc][0];
            if (in_array($info[6], ["link", "file"])){
                $info = $this->checkSchemLink($info, $oldItems[$inc], $this->modelName);                
            }
            
            $this->updCol ($id, $info);
        }
        
        foreach ($toAdd as $id => $info){
            $id = $info[0];
            if (in_array($info[6], ["link", "file"])){
                $info = $this->checkSchemLink($info, [], $this->modelName);                
            }
            
            $this->addCol ($info, $id);
        }
        

        //return [count($toAdd), count($toUpd), count($toDel)];
        return ["new" => $newItems, "old" => $oldItems, count($toSkip), count($toAdd), count($toUpd), count($toDel)];

    }
    

    protected function isEqual ($old, $new)
    {
        $isEqual = false;

        if (isset($new[1])) unset($new[1]);
        if (isset($old[1])) unset($old[1]);

        $jsonOld = json_encode($old, JSON_UNESCAPED_UNICODE);
        $jsonNew = json_encode($new, JSON_UNESCAPED_UNICODE);

        if ($jsonOld === $jsonNew) {
            $isEqual = true;
        }

        return $isEqual;
    }


    protected function checkSchemLink($cureInfo, $oldInfo, $cureModel)
    {        
        $linkModelName = count($cureInfo) > 0 ? $cureInfo[11][0] :  $oldInfo[11][0];
        $linkModel = new TDM ($linkModelName, $this->modelPath); 
        
        //Если удаляем
         if (count($cureInfo) == 0){
            $idToDel = $oldInfo[11][2];
            $linkModel->schem->delCol($idToDel);
            return;
        }

        $cureId = $cureInfo[0];
        $newColName = $cureModel .'_link';
        $newColInfo[6] = "link";
        $newColInfo[11] = [$cureModel, ["id"], $cureId];

        

        //Если добавляем
        if (count($oldInfo) == 0){
            $cureInfo[11][2] = $linkModel->schem->addCol($newColInfo, $newColName, true);
            return $cureInfo;
        }

       

        //Если редактируем
        $oldLinkInfo = $oldInfo[11];
        $oldId = $oldInfo[0];
        $foundId = false;
        
        foreach ($linkModel->getSchem() as $lsId => $lsInfo){
            $linkInfo = $lsInfo[11];

            if (!in_array($lsInfo[6], $this->linkTypes)) continue;
            if (!isset($linkInfo[0]) or $linkInfo[0] != $cureModel) continue;
            if (!isset($linkInfo[2]) or $linkInfo[2] != $oldId) continue;
            
            //Если совпадение по cureModel и oldId
            if ($cureId != $oldId) {
                //Обновляем ссялку если cureId изменился
                $linkModel->schem->updCol($lsId, $newColInfo);                
            } 
            $foundId = $lsId;

        }

        if (!$foundId){
            $cureInfo[11][2] = $linkModel->schem->addCol($newColInfo, $newColName, true);            
        } else {
            $cureInfo[11][2] = $foundId;
        }        

        return $cureInfo;
    }
   
}
