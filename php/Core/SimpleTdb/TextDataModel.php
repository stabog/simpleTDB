<?php


namespace SimpleTdb;

use SimpleTdb\TextDataBase as TDB;
use SimpleTdb\TextDataSchem as TDS;
use SimpleTdb\FormHelpers as FH;
use SimpleTdb\TextDataModelException;

class TextDataModel {
    protected $dbName = "";
    protected $dbPath = "";
    protected $indexType = "";
    protected $convertToDict = false;
    protected $convertProps = [];

    protected $data;
    public $schem;
    public $form;
    protected $models = [];

    protected $schemItems = [
        'id' => ['id', [], 0, 'Id колонки', true, true, 'text', '', '', [], [], []],
        'ausData' => ['ausData', [], 1, 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
    ];    
    

    public function __construct(string $dbName='', string $dbPath='', string $indexType=''){
        $this->dbName = ($dbName != '')? $dbName : $this->dbName;
        $this->dbPath = ($dbPath != '')? $dbPath : $this->dbPath;
        $this->indexType = ($indexType != '')? $indexType : $this->indexType;        
        

        $this->data = TDB::getInstance($this->dbName, $this->dbPath, $this->indexType);
        $this->schem = new TDS($this->dbName, $this->dbPath, $this->schemItems);
        $this->form = new FH();
    }
    
    public function setRespFormatToDict($idToKeys = false) {
        $this->convertToDict = true;
        if ($idToKeys) $this->convertProps['idToKeys'] = true;
    }

    public function setDependencies(array $dependencies) {
        $this->models = $dependencies;
        $this->schem->setModels($this->models);
    }

    public function showDependency() {
        return  "Class " . static::class . " has dependencies: " . implode(', ', array_keys($this->models)) . "\n";
    }


    public function getDbName ()
    {
        return $this->dbName;
    }

    public function all($filters = [])
    {        
        $data = $this->data->all($filters);
        if ($this->convertToDict){
            $data = $this->schem->convertListItemsToDict($data, $this->convertProps);
        }
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
        
        if ($this->convertToDict){
            $item = $this->schem->convertListItemToDict($item);
        }
        return $item;
    }


    public function add($info, $surce="user")
    {        
        if (!$info ) {
            throw new TextDataModelException("Не корректные данные для add.");
        }
        
        $info = $this->schem->checkValueBySchem($info, $surce);
        $newId = $this->data->add($info);

        if ($newId) {
            $this->updateLinkedBasesNew($newId, $info);
            return $newId;
        }

        return false;
    }

    

    public function upd($id, $info, $surce="user", $checkLinks=true)
    {   
        $lastInfo = $this->get($id);

        if (!$info ) {
            throw new TextDataModelException("Не корректные данные для upd.");
        }
        $info = $this->schem->checkValueBySchem($info, $surce);        

        if ($this->data->upd($id, $info)){
            if ($checkLinks){
                $this->updateLinkedBasesNew($id, $info, $lastInfo);
            }            
            return true;
        }

        return false;
    }    


    public function del($id)
    {        
        $lastInfo = $this->get($id);
        
        if ($this->data->del($id)){
            $this->updateLinkedBasesNew($id, [], $lastInfo);            
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
        //Получаем массив элементов для обновления
        $listToUpd = $this->getItemsToUpdate ($cureId, $info, $lastInfo);

        /*
        echo 'Обновляем связанные базы<br>';
        print_r($itemsToUpd);
        echo '<br>';
        */        

        foreach ($listToUpd as $modelName => $acts){
            $linkedModel = $this->models[$modelName];            
            $itemsToUpd = [];

            foreach ($acts as $type => $items){

                foreach ($items as $itemId => $params){
                    $colId = $params[0];
                    $value = $params[1];
                    $cureItem = $linkedModel->get($itemId);
                    $cureItem[$colId] = $cureItem[$colId] ?? [];
                    $cureItem[$colId] = $linkedModel->schem->convertToArray($cureItem[$colId]);
                    

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
            echo '<br>'.$modelName.'<br>';
            print_r($itemsToUpd);

            //$linkedModel->updItems($itemsToUpd);
        }
    }

    protected function getItemsToUpdate ($cureItemId, $info, $lastInfo)
    {
        $linkedFields = $this->schem->getLinkedFields();
        if (count($linkedFields) == 0) return [];

        $itemsToUpd = [];

        foreach ($linkedFields as $sId => $sInfo){

            $modelName = $sInfo[16][0] ?? '';
            $modelSchemId = $sInfo[16][2] ?? '';
            
            if ($modelName === '') continue;
            if ($modelSchemId === '') continue;
            if (!isset($this->models[$modelName])) continue;            

            $model = $this->models[$modelName];            
            $schemItems = $model->schem->getSchem();
            
            if (!isset($schemItems[$modelSchemId])) continue;

            $infoId = $schemItems[$modelSchemId][3] ?? '';

            if ($infoId === '') continue;
            
            $itemId = $sInfo[3];            
            
            //Элементы, в которые нужно добавить
            if (count($info) > 0) {
                $cureVals = $info[$itemId];
                foreach ($cureVals as $linkedId){
                    $itemsToUpd[$modelName]["add"][$linkedId] = [$infoId, $cureItemId];
                }
            }
            
            //Элементы, из которых нужно удалить
            if (count($lastInfo) > 0) {
                $lastVals = $lastInfo[$itemId] ?? [];
                $lastVals = $model->schem->convertToArray($lastVals);
                $keysToDel = array_diff($lastVals, $cureVals);
                foreach ($keysToDel as $linkedId){
                    $itemsToUpd[$modelName]["del"][$linkedId] = [$infoId, $cureItemId];
                }
            }
        }

        return $itemsToUpd;

    }


    public function getSchem ()
    {        
        $data = $this->schem->getSchem();
        $props = $this->convertProps;
        $props["isSchem"] = true;
        if ($this->convertToDict){
            $data = $this->schem->convertListItemsToDict($data, $props);
        }
        return $data;
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