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
    protected $convertedItems;
    protected $updByItem = false;
    protected $isUpdatingSchem = false;

    /*
    id => [
        id
        ausData => [add_time, add_user, upd_time, upd_user, snc_time, snc_user]
        tag: text
        title: text
        isSystem: bool
        isUnic: bool        
        dataType: text ?? one of mainTypes
        dataSubType: text ?? one of subTypes
        parentId: numb
        order: numb 
        items: dict        
        linkProps: list
        fieldInfo => [
            fieldTypeId: numb  <- зависит от dataType
            fieldViewTypeId: numb <- зависит от fieldType
            fieldPlaceholder: text 
            fieldDescription: text
            fieldOrder: numb
            fieldDefaultVal <- зависит от dataType
            fieldProps: list
        ]
    ]
    */


    protected $schemItems = [
        0  => [0, [], 'id', 'Id колонки', false, true, 'text', '', '', 1, [], [], []],
        1  => [1, [], 'ausData', 'Информация о создании/редактировании', true, false, 'list', '', '', 2, [], [], []],
        2  => [2, [], 'tag', 'Тэг колонки', true, false, 'numb', '', '', 3, [], [], []],
        3  => [3, [], 'title', 'Имя колонки', false, false, 'text', '', '', 4, [], [], []],
        4  => [4, [], 'isSystem', 'Колонка является системной', true, false, 'bool', '', '', 5, [], [], []],
        5  => [5, [], 'isUnic', 'Значение должно быть уникальным', false, false, 'bool', '', '', 6, [], [], []],
        6  => [6, [], 'dataType', 'Тип данных', false, false, 'text', '', '', 7, [], [], []],
        7  => [7, [], 'dataSubType', 'Подтип данных', false, false, 'numb', '', '', 8, [], [], []],
        8  => [8, [], 'parentId', 'Id родительского элемента', false, false, 'text', '', '', 9, [], [], []],
        9  => [9, [], 'order', 'Порядок полей', false, true, 'numb', '', '', 10, [], [], []],
        10 => [10, [], 'items', 'Текущие элементы для выбора', false, false, 'list', '', '', 11, [], [], []],        
        11 => [11, [], 'linkProps', 'Настройки для связи', false, false, 'list', '', '', 12, [], [], []],
        12 => [12, [], 'fieldInfo', 'Настройки поля для формы', false, false, 'list', '', '', 13, [], [], []],
        
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
        "dict" => ['Словарь'],
        "link" => ['Связь'],
        "file" => ['Файл'],
        "grup" => ['Группа'],
        "ligr" => ['Список групп'],
        "digr" => ['Словарь групп'],
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
    }    


    public function getSchem ($newType="data", $update=false)
    {
        if (!$this->convertedItems or $update){        
            $items = $this->data->all();
            $schem = $this->schemItems;
            $this->convertedItems = $this->validateAndConvertItems ($items, $schem, "data", $newType);
        }
        return $this->convertedItems;
    }

    public function getLinkedFields ()
    {
        $result = [];
        $schem = $this->schemItems;
        foreach ($this->getSchem () as $sInfo){
            if (!in_array($sInfo[6], $this->linkTypes)) continue;
            $result[$sInfo[0]] = $sInfo;
        }
        $result = $this->validateAndConvertItems ($result, $schem, "data","dict");
        return $result;
    }

    

    public function validateAndConvertItems ($items, $schem, $cureType="data", $newType="data", $assignKeys=false)
    {
        $result = [];
        foreach ($items as $itemId => $itemInfo){
            if (!is_array($itemInfo) or count($itemInfo) == 0) continue;

            $checkedItem = $this->validateAndConvertItemValues ($itemInfo, $schem, $cureType, $newType);
            
            if ($assignKeys){
                $result[$itemId] = $checkedItem;
            } else {
                $result[] = $checkedItem;
            }
            
        }
        
        return $result;
    }

    public function validateAndConvertItemValues ($itemInfo, $schem, $cureType="data", $newType="data", $schemUpdate=false, $showHash = false)
    {        
        if ($schemUpdate){
            $this->updateSchemByItem ($itemInfo);
            $schem = $this->getSchem ($newType="data", $update=true);
        }
        
        $result = [];

        foreach ($schem as $sId => $sInfo){
            $dataId = $sId;
            $dataTag = $sInfo[2];
            $dataType = $sInfo[6];

            if ($dataTag === 'passHash' and !$showHash){
                continue;
            }

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

            /*
            if ($this->modelName == 'sess' and $newType == 'dict'){
                echo $returnKey.' '.$newType.'<br>';
                print_r($sInfo);                
            }
            */

            $result[$returnKey] = $cureVal;
        }

        
        
        
        return $result;
    }
    
    protected function updateSchemByItem ($itemInfo)
    {
        $cureTags = array_column($this->data->all(), 2);

        foreach ($itemInfo as $key => $value){
            if (!in_array($key, $cureTags)){
                $info[2] = $key;
                $this->addCol($info, $modifyTag=false);
            }           
        }
    }
    


    public function addCol ($info, $modifyTag=true)
    {
        
        $tag = $origTag = isset($info[2]) ? $info[2] : "";

        if ($modifyTag){
            $tag = $origTag = $this->convertToValidVariableName($origTag);
        }
        
        
        //Проверка на существование поля с таким tag
        $cureTags = array_column($this->data->all(), 2);
        while (in_array($tag, $cureTags)){
            $tag = $origTag . rand(100, 999);
        }        
        
        $title        = isset($info[3])  ? $info[3] : $origTag;
        $isSystem     = isset($info[4])  ? $info[4] : false;
        $isUnic       = isset($info[5])  ? $info[5] : false;
        $dataType     = isset($info[6])  ? $info[6] : 'text';
        $dataSubtype  = isset($info[7])  ? $info[7] : '';        
        $parentId     = isset($info[8])  ? $info[8] : '';
        $items        = isset($info[9])  ? $info[9] : [];
        $fieldInfo    = isset($info[10]) ? $info[10] : [];
        $linkProps    = isset($info[11]) ? $info[11] : [];        
        
        $tobase = [
            2 => $tag,
            3 => $title,
            4 => $isSystem,
            5 => $isUnic,
            6 => $dataType,
            7 => $dataSubtype,
            8 => $parentId,
            9 => $items,
            10 => $fieldInfo,
            11 => $linkProps,
        ];

        $colId = $this->data->add($tobase);
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

    // Функция для преобразования строки в корректное название переменной
    protected function convertToValidVariableName(string $input): string
    {
        // Транслитерация русских символов в латиницу
        $translit = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
            'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];
        $input = mb_strtolower($input, 'UTF-8');
        $input = strtr($input, $translit);

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
        $schem = $this->schemItems;
        $cureItems = $this->data->all();

        $newItems = $this->validateAndConvertItems ($items, $schem, "dict", "data");        
        $oldItems = $this->validateAndConvertItems ($cureItems, $schem, "data", "data", true);

        
        $toDel = $oldItems;        

        foreach ($newItems as $itemInfo){
            $itemId = isset($itemInfo[0])? $itemInfo[0] : "";

            if (!isset($oldItems[$itemId])){
                $toAdd[] = $itemInfo;
                continue;
            }            

            if ($this->isEqual($oldItems[$itemId], $itemInfo)){
                $toSkip[$itemId] = $itemInfo;
            } else {
                $toUpd[$itemId] = $itemInfo;
            }

            unset($toDel[$itemId]);
        }

        //return ["new" => $toAdd, "old" => $oldItems, count($toSkip), count($toAdd), count($toUpd), count($toDel)];

        foreach ($toDel as $id => $info){
            if (in_array($info[6], $this->linkTypes)){
                $info = $this->checkSchemLink([], $oldItems[$id], $this->modelName);                
            }
            $this->delCol ($id);
        }

        foreach ($toUpd as $id => $info){
            $oldType = $oldItems[$id][6];
            //Если старый тип ссылка а новый нет - удаляем связь
            if (in_array($oldType, $this->linkTypes) and $info[6] != $oldType) {
                $this->checkSchemLink([], $oldItems[$id], $this->modelName);
                $info[11] = [];
            }
            if (in_array($info[6], $this->linkTypes)){                
                $info = $this->checkSchemLink($info, $oldItems[$id], $this->modelName);                
            }
            
            $this->updCol ($id, $info);
        }
        
        foreach ($toAdd as $info){
            $newId = $this->addCol ($info);
            
            if (in_array($info[6], $this->linkTypes)){
                $info[0] = $newId;
                $info = $this->checkSchemLink($info, [], $this->modelName);
                $this->updCol ($newId, $info);
            }            
        }
        

        //return [count($toAdd), count($toUpd), count($toDel)];
        return ["skipped" => count($toSkip), "added" => count($toAdd), "updated" => count($toUpd), "deleted" => count($toDel)];

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
        $newColInfo[2] = $cureModel .'_link';
        $newColInfo[6] = "link";
        $newColInfo[11] = [$cureModel, ["id"], $cureId];        

        //Если добавляем
        if (count($oldInfo) == 0){
            $cureInfo[11][2] = $linkModel->schem->addCol($newColInfo);
            return $cureInfo;
        }       

        //Если редактируем        
        $foundId = false;
        $linkModelSchem = $linkModel->schem->getSchem();

        foreach ($linkModelSchem as $lsInfo){
            

            $lsId = $lsInfo[0];
            $linkInfo = $lsInfo[11] ?? [];

            if (!in_array($lsInfo[6], $this->linkTypes)) continue;
            if (!isset($linkInfo[0]) or $linkInfo[0] != $cureModel) continue;
            if (!isset($linkInfo[2]) or $linkInfo[2] != $cureId) continue;
            
            $foundId = $lsId;
            break;
        }

        if (!$foundId){
            $cureInfo[11][2] = $linkModel->schem->addCol($newColInfo);            
        } else {
            $cureInfo[11][2] = $foundId;
        }        

        return $cureInfo;
    } 

    

    public function fillLinkedItems(array $schemDict): array
    {
        foreach ($schemDict as &$field) {
            if (
                in_array($field['dataType'], $this->linkTypes)
                && !empty($field['linkProps'])
                && !empty($field['linkProps'][0])
            ) {
                $linkedModelName = $field['linkProps'][0];
                $linkedFields = $field['linkProps'][1] ?? ['id'];
                $linkedModel = new TDM($linkedModelName, $this->modelPath);
                $all = $linkedModel->all();                
                $items = [];
                foreach ($all as $row) {
                    $rowId = $row['id'] ?? $row[0];
                    $display = [];
                    foreach ($linkedFields as $f) {
                        $display[] = $row[$f] ?? '';
                    }
                    $items[] = ["id"=>$rowId, "title"=> trim(implode(' ', $display))];
                }
                //print_r($items);
                $field['items'] = $items;
            }
        }
        return $schemDict;
    }
}
