<?php


namespace SimpleTdb;

use SimpleTdb\TextDataBase as TDB;
use SimpleTdb\TextDataSchem as TDS;
use SimpleTdb\TextDataModelException;

class TextDataModel {
    protected $dbName = "";
    protected $dbPath = "";
    protected $indexType = "";
    protected $convertToDict = false;
    protected $schemUpdate = false;
    protected $convertProps = [];

    protected $data;
    public $schem;
    public $form;
    protected $models = [];

    protected $schemItems = [
        0 => [0, [], 'id', 'Id', true, true, 'text', '', '', [], [], []],
        1 => [1, [], 'ausData', 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
        2 => [2, [], 'title', 'Название', false, false, 'text', '', '', [], [], []],
    ];    
    

    public function __construct(string $dbName='', string $dbPath='', string $indexType='', bool $schemUpdate=false){
        $this->dbName = ($dbName != '')? $dbName : $this->dbName;
        $this->dbPath = ($dbPath != '')? $dbPath : $this->dbPath;
        $this->indexType = ($indexType != '')? $indexType : $this->indexType;

        $this->data = TDB::getInstance($this->dbName, $this->dbPath, $this->indexType);
        $this->schem = new TDS($this->dbName, $this->dbPath, $this->schemItems);
        $this->schemUpdate = $schemUpdate;

        $this->setModels();
    }
    
    public function setRespFormatToDict(array $props=[]) {
        $this->convertToDict = true;
        foreach ($props as $key => $value ){
            if ($value) $this->convertProps[$key] = $value;
        }
    }

    

    protected function setModels()
    {
        /*
        foreach ($this->schem->getLinkedFields() as $sInfo){
            if (!isset($sInfo[11]) or !is_array($sInfo[11]) or $sInfo[11][0] == '') continue;
            $modelName = $sInfo[11][0];
            $this->models[$modelName] = new self($modelName, $this->dbPath);
        }
        */
    }

    /*
    public function setDependencies(array $dependencies) {
        $this->models = $dependencies;
        $this->schem->setModels($this->models);
    }

    public function showDependency() {
        return  "Class " . static::class . " has dependencies: " . implode(', ', array_keys($this->models)) . "\n";
    }
    */


    public function getDbName ()
    {
        return $this->dbName;
    }

    public function all($filters = [])
    {        
        $data = $this->data->all();
        $data = $this->schem->validateAndConvertItems ($data, $this->schem->getSchem(), "data", "dict");

        return $data;
    }


    public function get($id)
    {        
        if (!$id ) {
            throw new TextDataModelException("ID не указан.");
        }

        $item = $this->data->get($id);
        if (!$item){
            throw new TextDataModelException("Элемент '$id' не найден в базе.");
            return false;
        }
        
        
        $item = $this->schem->validateAndConvertItemValues ($item, $this->schem->getSchem(), "data", "dict", false);
        return $item;
    }


    public function add($info, $surce="user")
    {        
        if (!$info ) {
            throw new TextDataModelException("Не корректные данные для add.");
        }
        
        $info = $this->schem->validateAndConvertItemValues ($info, $this->schem->getSchem(), "dict", "data", $this->schemUpdate);
        $newId = $this->data->add($info);
        
        if ($newId) {
            $this->updateLinkedBasesNew($newId, $info);
            return $newId;
        }

        return false;
    }

    

    public function upd($id, $info, $surce="user", $checkLinks=true)
    {   
        $lastInfo = $this->data->get($id);

        if (!$info ) {
            throw new TextDataModelException("Не корректные данные для upd.");
        }
        
        $convertProps = [];
        if($surce!= "user") $convertProps["converAll"] = true;
        $info = $this->schem->validateAndConvertItemValues ($info, $this->schem->getSchem(), "dict", "data", $this->schemUpdate);

        if ($this->data->upd($id, $info)){            
            $this->updateLinkedBasesNew($id, $info, $lastInfo);
            /*
            if ($checkLinks){
                
            }
            */
            return true;
        }

        return false;
    }    


    public function del($id)
    {        
        $lastInfo = $this->get($id);
        
        if ($this->data->del($id)){
            //$this->updateLinkedBasesNew($id, [], $lastInfo);            
            return true;
        }
        
        return false;
    }


    public function addItems($items)
    {        
        return $this->data->addItems($items);       
    }

    public function updItems($items)
    {        
        return $this->data->updItems($items);
    }    

    public function delItems($keys)
    {
        return $this->data->delItems($keys);
    }


    public function modifyImportItem($item)
    {
        foreach ($this->schem->getSchem() as $sId => $sInfo){
            $itemId = $sInfo[3];
            $type = $sInfo[6];            

            if (!isset($item[$itemId])) continue;

            if ($type == 'arra'){
                if (!is_array($item[$itemId])) $item[$itemId] = [$item[$itemId]];
            } else if ($type == 'time'){
                if ($item[$itemId] != '' and !is_numeric($item[$itemId])) $item[$itemId] = strtotime($item[$itemId]);
                //echo $item[$itemId].'<br>';
            }
        }
        return $item;
    }
    
    

    
    
    public function updateLinkedBasesNew($cureId, $info, $lastInfo=[])
    {
        $linkedFields = $this->schem->getLinkedFields();
        //print_r( $linkedFields);

        $this->setLinkedModels ($linkedFields);
        
        //Получаем массив элементов для обновления
        $listToUpd = $this->getItemsToUpdate ($linkedFields, $cureId, $info, $lastInfo);
        

        //$this->updateLinkedBases($listToUpd);

        
    }

    protected function setLinkedModels ($linkedFields){
        //foreach ($this->schem->getLinkedFields() as $sInfo){
        foreach ($linkedFields as $sInfo){
            if (!isset($sInfo["linkProps"]) or !is_array($sInfo["linkProps"])) continue;
            if ($sInfo["linkProps"][0] == '') continue;
            $modelName = $sInfo["linkProps"][0];
            $this->models[$modelName] = new self($modelName, $this->dbPath);
        }
    }

    
    protected function getItemsToUpdate ($linkedFields, $cureItemId, $info, $lastInfo)
    {
        $itemsToUpd = [];
        foreach ($linkedFields as $sInfo){

            $modelName = $sInfo["linkProps"][0] ?? '';
            if (!isset($this->models[$modelName])) continue;
            $linkModel = $this->models[$modelName];

            $linkSchemId = $sInfo["linkProps"][2] ?? '';
            if ($linkSchemId === '') continue;

            $linkSchem = $linkModel->schem->getSchem ("data");
            if (!isset($linkSchem[$linkSchemId])) continue;
            

            //Получаем все id из текущего поля
            $cureModelId = $sInfo["id"];
            //$items = $info[$cureModelId] ?? [];
            $items =  isset($info[$cureModelId])? $this->schem->convertToArray($info[$cureModelId]) : [];
            $lastItems = $lastInfo[$cureModelId] ?? [];
            $lastItems =  $this->schem->convertToArray($lastItems);            

            /*
            print_r($items);
            print_r($lastItems);
            echo '<hr>';
            */

            $itemsToAdd = array_diff($items, $lastItems);
            $itemsToDel = array_diff($lastItems, $items);

            //print_r($itemsToAdd);
            //print_r($itemsToDel);
            

            //Добавляем новые связи
            if (count($itemsToAdd) > 0){
                foreach ($itemsToAdd as $linkedId){
                    $itemsToUpd[$modelName]["add"][$linkedId] = [$linkSchemId, $cureItemId];
                    $this->updLinkModel ($modelName, $linkedId, $linkSchemId, $cureItemId, "add");
                }
            }

            //Удаляем старые связи
            if (count($itemsToDel) > 0){
                foreach ($itemsToDel as $linkedId){
                    $itemsToUpd[$modelName]["del"][$linkedId] = [$linkSchemId, $cureItemId];
                    $this->updLinkModel ($modelName, $linkedId, $linkSchemId, $cureItemId, "del");
                }
            }
        }

        return $itemsToUpd;

    }

    protected function updLinkModel ($linkModelName, $linkItemId, $linkDataId, $cureId, $act){
        $linkModel = $this->models[$linkModelName];
        $linkItem = $linkModel->data->get($linkItemId);

        if (!$linkItem) return;
        
        $linkItem[$linkDataId] = $linkItem[$linkDataId] ?? [];
        $linkItemCell = $linkItem[$linkDataId];
        $linkItemCell = (is_array($linkItemCell)) ? $linkItemCell : [$linkItemCell];
        $linkItemCell = array_diff($linkItemCell, [""]);

        if ($act === 'add'){
             if (!in_array($cureId, $linkItemCell)){
                $linkItemCell[] = $cureId;
             }
        }
        if ($act === 'del'){
            if (in_array($cureId, $linkItemCell)){
                $key = array_search($cureId, $linkItemCell);
                unset($linkItemCell[$key]);
                $linkItemCell = array_values($linkItemCell);
            }
        }

        if ($linkItemCell != $linkItem[$linkDataId]){
            $linkItem[$linkDataId] = $linkItemCell;
            $linkModel->data->upd($linkItemId, $linkItem);
        }

    }

    protected function updateLinkedBases ($listToUpd)
    {
        //echo 'Обновляем связанные базы<br>';
        //print_r($itemsToUpd);
        //echo '<br>';
        
        //Обработка на уровне данных
        //[model][act][items]
        foreach ($listToUpd as $modelName => $acts){
            // Создаем новую модель с именем $modelName
            $linkedModel = $this->models[$modelName];
            $itemsToUpd = [];

            foreach ($acts as $type => $items){

                foreach ($items as $itemId => $params){
                    $colId = $params[0];
                    $value = $params[1];
                    $cureItem = $linkedModel->get($itemId);
                    $cureItem[$colId] = $cureItem[$colId] ?? [];
                    //$cureItem[$colId] = $linkedModel->schem->convertToArray($cureItem[$colId]);
                    

                    if ($type == "add" and !in_array($value, $cureItem[$colId])){
                        $cureItem[$colId][] = $value;
                        $_SESSION["message"][] = ['suc', 'Добавили элемент ['.$value.'] в ['.$modelName.']->['.$itemId.']->['.$colId.'].'];
                        
                    }

                    if ($type == "del"  and in_array($value, $cureItem[$colId])){                        
                        $key = array_search($value, $cureItem[$colId]);
                        unset($cureItem[$colId][$key]);
                        $_SESSION["message"][] = ['suc', 'Удалили элемент ['.$key.'=>'.$value.'] из ['.$modelName.']->['.$itemId.']->['.$colId.'].'];
                        
                    }

                    $linkedModel->upd($itemId, $cureItem, false);
                    $itemsToUpd[] = $cureItem;
                }
                
            }
            //echo '<br>'.$modelName.'<br>';
            //print_r($itemsToUpd);

            //$linkedModel->updItems($itemsToUpd);
        }
    }
    


    public function getSchem ()
    {        
        $schemDict = $this->schem->getSchem("dict");
        $schem = $this->schem->fillLinkedItems($schemDict);

        /*
        $props = $this->convertProps;
        $props["isSchem"] = true;
        if ($this->convertToDict){
            $data = $this->schem->convertListItemsToDict($data, $props);
        }
        */
        return $schem;
    }

    public function saveSchem ($items)
    {                
        return $this->schem->saveSchem($items);
    }



    

    /*

    public function updateLinkedBases($cureId, $info)
    {
        $linkedFields = $this->schem->getLinkedFields();
        
        if (count($linkedFields) == 0) return null;        
        
        foreach ($linkedFields as $sId => $sInfo){
            $itemToUpd = [];
            
            [$lModel, $lColId] = $this->getLinkedModel($sInfo);
            if (!$lModel) continue;

            //Получаем id элементов для добавления текущего
            $colId = $sInfo[3];
            $linkedModelIds = $info[$colId] ?? [];

            //Получаем id элементов для удаления
            $idsToClean = [];
            foreach ($lModel->all() as $itemId => $itemInfo){
                //Пропускаем:
                //Если в текущем списке связанных элементов есть itemId
                if (in_array($itemId, $linkedModelIds)) {
                    continue;
                }
                //Если колонка в связанном элементе не задана или не массив
                if (!isset($itemInfo[$lColId]) or !is_array($itemInfo[$lColId])) {
                    continue;
                }            

                //Если в колонке связанном элементе нет cureId
                if (!in_array($cureId, $itemInfo[$lColId])) {
                    continue;
                }

                $idsToClean[] = $itemId;
            }

            
            if (count($idsToClean) > 0) {
                $linkedModelIds = array_merge($linkedModelIds, $idsToClean);
            }

            //Проходимся по полученным элементам
            //echo $cureId.'<br>';
            foreach ($linkedModelIds as $id){
                $cureItem = $lModel->get($id);
                //print_r($cureItem);
                //echo '<br>';

                $cureItem[$lColId] = $cureItem[$lColId] ?? [];
                $cureValues = $newValues = $this->checkIsArray($cureItem[$lColId]);
                //print_r($cureValues);
                //echo '<hr>';                

                if (count($idsToClean) > 0 and in_array($id, $idsToClean)){
                    
                    $keyToDel = array_search($cureId, $newValues);
                    unset($newValues[$keyToDel]);
                    $newValues = array_diff($newValues,[""]);

                    //print_r($cureValues);
                    //echo '<br>';
                    //echo 'Удаляем ['.$cureId.'] из ['.$id.']<br>';
                    //print_r($newValues);
                    //echo '<br>';
                    //sort($cureValues);

                } else {                    
                    if (in_array($cureId, $newValues)) continue;
                    //echo 'Добавляем ['.$cureId.'] в ['.$id.'] '.$lModel->getDbName().'<br>';
                    $newValues[] = $cureId;
                }

                if ($newValues != $cureValues){
                    

                    $cureItem[$lColId] = $newValues;

                    
                    print_r($cureItem);
                    echo '<br>';
                    print_r($cureItem);
                    echo '<hr>';
                    

                    $itemToUpd[$id] = $cureItem;
                }                
            }
            
            if (count($itemToUpd) > 0) {
                //print_r($itemToUpd);
                //echo '<br>';
                $lModel->updItems($itemToUpd);
            }
        }
    }

    public function getLinkedModel ($schemInfo)
    {
        if (!isset($schemInfo[16]) or !is_array($schemInfo[16])){
            return false;
        }

        //Получаем модель для обновления
        $modelName = $schemInfo[16][0];

        //Проверка на наличие модели (для загрузки файлов)
        if (!isset($this->models[$modelName])) {
            return ["", ""];
        }

        $model = $this->models[$modelName];
        


        //Получаем id колонки для обновления
        $schemId = $schemInfo[16][2];        
        $schemItems = $model->schem->getSchem();

        //Проверка на наличие поля в связанной схеме
        if (!isset($schemItems[$schemId])) {
            return ["", ""];
        }

        $schemItem = $schemItems[$schemId];
        $colId = $schemItem[3];

        return [$model, $colId];
    }

    public function checkIsArray($value): array
    {        
        if (is_array($value)) return $value;

        if ($value = '') return [];

        $value = [$value];
        $value = array_diff($value,[""]);
        return $value;
    }

    */
}