<?php

namespace SimpleTdb;

use SimpleTdb\TextDataBase as TDB;
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

    public $arrayTypes = ['arra', 'file', 'link', 'list'];
    

    public function __construct($modelName, $modelPath, $modelSchem)
    {
        $this->modelName = $modelName;
        $this->modelSchem = $modelSchem;

        $schemDbName = $this->modelName.'_schem';
        $this->data = TDB::getInstance($schemDbName, $modelPath, "str");        
        
        //Обновляем информацию о схеме из модели в БД
        if (count ($this->modelSchem) > 0){
            foreach ($this->modelSchem as $id => $info){
                if (!$this->data->get($id)){
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
            $itemInc = $itemInfo[2];
            $lastInc = ($itemInc > $lastInc)? $itemInc : $lastInc;
        }
        return $lastInc;
    }



    public function addCol ($name, $info=[])
    {

        $tag = $this->convertToValidVariableName($name);


        //Проверка на существование поля с таким именем
        if (isset($this->data->all()[$tag])){
            $_SESSION["message"][] = ['war', 'Колонка с именем ['.$tag.'] уже существует.'];
            return $tag;
        }

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
        
        $this->lastInc ++;
        $tobase = [
            0 => $tag, //id
            1 => [], //system            
            2 => $this->lastInc, //increment
            3 => $name, //Title
            4 => false, //isSystem
            5 => false, //isUnic
            6 => $info[6] ?? 'text', //dataType
            7 => '',   //dataSubtype
            8 => '',   //parentId
            9 => [],   //items
            10 => [],   //fieldInfo
            11 => $info[11] ?? [], //linkProps
        ];
        $colId = $this->data->add($tobase, $tag);
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

    public function convertToArray($value)
    {
        if (!is_array($value)){
            $value = ($value == '')? [] : [$value];
        }
        //$value = array_filter($value, fn($item) => ($item == '')? false : true);

        return $value;
    }


    


    // Проверяем значения по схеме, дополняем схему
    public function checkValueBySchem($info, $surce="user")
    {
        $result = [];
        $schemNames = array_column($this->getSchem(), 0);
        
        $processedInfo = [];
        foreach ($info as $infoId => $value) {
            $colName = $infoId;
            if (!in_array($infoId, $schemNames) and $value != '') {
                $colName = $this->addCol($infoId);                
            }
            $processedInfo[$colName] = $value;
        }

        foreach ($this->getSchem() as $sId => $sInfo) {
            if ($sInfo[4]) {
                //Пропускаем системную колонку
                if ($surce == "user") continue;
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

            // Если link, arra, file
            if (in_array($type, $this->linkTypes)) {
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
            $cureVal = '';
            //Получаем инфо по colId
            if (isset($item[$colId])) $cureVal = $item[$colId];

            // Если link, arra, file
            if (in_array($type, $this->linkTypes)) {
                if (!is_array($cureVal)) $cureVal = [$cureVal];
                $cureVal = array_diff($cureVal, [""]);
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
   
}
