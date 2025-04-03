<?php

namespace SimpleTdb;

use Exception;

class TextDataImport
{
    protected $uploadPath = 'temp/';
    
    protected $model;
    protected $url;
    protected $modifyFunc;

    protected $post;
    protected $postFiles;

    protected $delimiters = [
        ';' => 'Точка с запятой (;)',
        ',' => 'Запятая (,)',
        '	' => 'Табуляция ("	")',
    ];

    protected $primaryTypes = [
        '1' => 'Значения для полей в базе',
        '2' => 'Значения для колонок в файле',
    ];

    protected $matchActs = [
        '1' => 'Пропускать',
        '2' => 'Заменять',
        '3' => 'Дополнять (в разработке)',
    ];

    protected $actions = [
        1 => 'Использовать для сравнения',
        2 => 'Пропускать',
        3 => 'Заменять',
        4 => 'Дополнять (в разработке)',
    ];

    public function __construct($model, $url)
    {
        $this->model = $model;
        $this->url = $url;
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["form_info"])){
            $this->post = $_POST["form_info"];
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES["form_info"])){
            $this->postFiles = $_FILES["form_info"];
        }
    }


    public function viewImport ($html = '')
    {
        $cureStage = $this->post['stage'] ?? 1;

        if ($cureStage == 1){

            return $this->viewFormSurce ();

        } else if ($cureStage == 2){

            if (isset($this->post['type']) and $this->post['type'] == 'api') {
                $this->getAPI();
            }

            $row = $this->importPrepare();
            if ($row){
                return $this->viewFormSetup ($this->model->schem->getSchem(), $row);
            }            

        } else if ($cureStage == 3){
            
            if ($this->setPrimaryId () === false) {
                $row = $this->importPrepare();
                return $this->viewFormSetup ($this->model->schem->getSchem(), $row);
            }
            
            $this->importProcess();
        }
    }



    public function getAPI ()
    {
        //print_r($this->post);

        $url = $this->post["ep"];
        $authType = "";
        $authKey = $this->post["key"];
        $container = $this->post["container"];

        // Инициализация cURL и Установка параметров запроса
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$authKey}"
        ]);

        // Выполнение запроса и получение ответа
        $response = curl_exec($ch);

        // Проверка на ошибки
        if (curl_errno($ch)) {
            echo 'Ошибка cURL: ' . curl_error($ch);
        } else {
            // Преобразование ответа в массив
            $response = json_decode($response, true);
        }

        // Закрытие cURL-сессии
        curl_close($ch);

        /*
        $skippingCols = ["HW_todo", "Password", "Diaries", "Quals", "Tags", "Sess_files", "Hwc_him", "Hwc_his", "Bm_items", "GC_add_change",
            "GC_manage", "GC_groups_couch", "GC_groups_part", "GC_izm_quests", "GC_izm_steps", "GC_izm_comms", "GC_izms", "DR_Comms",
            "DR_task_steps", "DR_logs", "created_at", "updated_at"
        ];
        */

        if ($container != ''){
            if (!isset($response [$container])){
                echo '<br>';
                print_r($response);
            } else {
                $data =  $response [$container];
            }

        } else {
            $data = $response;
        }

        // Проверка, что данные были успешно декодированы и являются массивом
        if (!is_array($data)) {
            echo '<br>';
            print_r($response);
        }

        // Сбор всех возможных заголовков
        $headers = [];
        foreach ($data as $row) {
            foreach (array_keys($row) as $key) {
                $headers[$key] = true;
            }
        }
        $headers = array_keys($headers);
        sort($headers);

        
        
        
        $new_name = md5(date("Y-m-d-h-s").rand(100, 1000)).'.csv';
        $filename = $this->uploadPath . $new_name;
        $file = fopen($filename, 'w'); // Открытие файла для записи        
        $delimiter = ";"; // Установка разделителя для CSV
        
        fputcsv($file, $headers, $delimiter); // Запись заголовков в CSV-файл        
        foreach ($data as $row) { // Запись данных в CSV-файл
            $line = [];
            foreach ($headers as $header) {
                $value = $row[$header] ?? '';                
                if (is_array($value)) $value = $this->flattenArray($value);
                $line[] = is_array($value) ? implode("||", $value) : $value;
            }
            fputcsv($file, $line, $delimiter);
        }
        
        fclose($file); // Закрытие файла
        $this->post["filePath"] = $filename;
    }


    

    public function importPrepare ()
    {
        if (isset($this->post["filePath"]) and $this->post["filePath"] != ''){
            $cureFilePath = $this->post["filePath"];
        } else {
            $tempPath = $this->uploadPath;
            $upload = new \UploadFilesHelper($tempPath, $this->postFiles);        
            $uploadFiles = $upload->uploadFiles();
            $cureFilePath = $uploadFiles["files"][0]["path"];            
            $this->post["filePath"] = $cureFilePath;

            $this->deleteOldFiles ($tempPath, 30);
        }

        
        $delimiter = $this->post["delimiter"] ?? ";";
        $hasHeader = $this->post["hasHeaders"] ?? false;
        

        $start = ($hasHeader) ? 1 : 0;
        $row = $this->readFileCsv ($cureFilePath, $delimiter, $start, 3)[0];

        if (count($row) == 0) return false;

        else if (count($row) === 1){
            $_SESSION["message"][]  = ['err', 'При экспорте найдена только одна колонка. Возможно, указан неправильный разделитель.'];
        } else {
            $_SESSION["message"][]  = ['suc', 'При экспорте найдены ['.count($row).'] колонки. Значения первой строки: <ol><li>'.implode("</li><li>", $row).'</li></ol>'];
        }
        
        return $row;
    }

    public function setPrimaryId ()
    {
        $actions = $this->post["actions"] ?? [];
        if (!in_array(1, $actions)) {
            $_SESSION["message"][]  = ['err', 'Не выбрано поле для сравнения'];
            return false;
        }
        return array_search(1, $actions);
    } 


    public function importProcess ($html='')
    {
        
        //print_r($this->post);

        
        // Фиксируем время начала выполнения функции
        $startTime = microtime(true);

        $html = '';

        $delimiter = $this->post["delimiter"] ?? ";";
        $hasHeader = $this->post["hasHeaders"] ?? false;
        $primaryType = $this->post["primaryType"] ?? 1;
        $cureFilePath = $this->post["filePath"] ?? '';
        $matchAct = $this->post["matchAct"] ?? 1;
        $relations = $this->post["relations"] ?? [];
        $actions = $this->post["actions"] ?? [];
        $delNotFound = $this->post["delNotFound"] ?? false;

        $items = $this->post["items"] ?? [];
        $rowExample = $this->readFileCsv ($cureFilePath, $delimiter, 0, 1);

        $exportCounts = [
            "add" => 0,
            "skip" => 0,
            "upd" => 0,
            "del" => 0,
        ];
        
        //Задаем действия для элементов базы
        foreach ($relations as $firstId => $secondId){
            $sId = $firstId;            
            if ($primaryType == 2) $sId = $secondId;

            if (!isset($actions[$firstId]) or $actions[$firstId] == '' and $secondId != 'add') continue;

            //Если нужно добавить колонку
            if ($secondId == 'add'){
                

                //Получаем имя новой колонки
                $newColName = $items[$firstId];

                //Добавляем колонку, получаем ее id и инкремент
                [$colId, $sId] = $this->model->schem->addCol ($newColName);

                //Устанавливаем связь колонки со схемой
                $relations[$firstId] = $sId;

                //Устанавливаем действие на заменить
                $actions[$firstId] =  ($actions[$firstId] != '') ? $actions[$firstId] : 2;

                
            }

            $act[$sId] = $actions[$firstId];

            if ($actions[$firstId] == 1){
                $primaryId = $sId;
            }
        }

        //echo '<br>';
        //print_r($act);

        


        
        //print_r($this->post);
        //echo '<hr>';
        //print_r($act);
        //echo '<br>'.$primaryId.'<br>';

        //получаем текущий массив уникальных значения
        $cureUnicVals = [];
        foreach ($this->model->all() as $id => $info){
            if (!isset($info[$primaryId])) continue;

            $cureUnicVals[$id] = $info[$primaryId];
        }

        if ($delNotFound){
            $itemsToDel = $cureUnicVals;
        }

        $start = ($hasHeader) ? 1 : 0;
        $data = $this->readFileCsv ($cureFilePath, $delimiter, $start);
        

        $itemsToAdd = [];
        
        foreach ($data as $rowId => $row){
            //if ($rowId > 60000) break;

            

            $item = [];
            foreach ($relations as $firstId => $secondId){
                $sId = ($primaryType == 2) ? $secondId : $firstId;
                $cId = ($primaryType == 2) ? $firstId : $secondId;

                if (!isset($row[$cId])) continue;
                $item[$sId] = $row[$cId];
            }

            //Пропускаем элементы у которых не задано поле для сравнения
            //echo $primaryId.'<br>';
            if (!isset($item[$primaryId]) or $item[$primaryId] == ''){
                $json = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
                $_SESSION["message"][]  = ['err', 'Пропущен элемент ['.$rowId.'], у которого отсутствует ['.$primaryId.'] значение для сравнения.<br>'.$json];
                continue;
            }

            $item = $this->modifyImportItem($item);
            $key = array_search($item[$primaryId], $cureUnicVals);
            

            if ($key === false){                
                $itemsToAdd[] = $item;
                $exportCounts["add"] ++;
                continue;                
            }

            //Собиаем Элементы для удаления
            if ($delNotFound){
                unset($itemsToDel[$key]);
            }
            
            

            //var_dump($matchAct);
            if ($matchAct === 1){
                $exportCounts["skip"] ++;
                continue;
            }

            $itemInBase = $this->model->get($key);
            //echo $rowId.": Найденый ключ " . ($key) . "<br>";
            
            $updatedItem = $this->updateExportItem($itemInBase, $item, $act);
            if ($updatedItem){
                $exportCounts["upd"] ++;
                $itemsToUpd[$key] = $item;
            } else {
                $exportCounts["skip"] ++;
            }            
    
            // Отправляем содержимое буфера и очищаем его
            //ob_flush();
            //flush();            
            
        }

        // Завершаем буферизацию вывода
        //ob_end_flush();

        if ($delNotFound){
            $keysToDel = array_keys($itemsToDel);
            $exportCounts["del"] = count($keysToDel);
        }

        $mess = '
        Обработано '.count($data).' элементов.<br>
        Длительность выполнения ' . (microtime(true) - $startTime) . ' секунд<br>
        Использовано памяти: ' .  $this->formatBytes(memory_get_usage()) . ' байт
        ';

        $_SESSION["message"][]  = ['suc', $mess];
        

        if ($exportCounts["add"] > 0){
            $this->model->addItems ($itemsToAdd);
            $_SESSION["message"][]  = ['suc', 'Добавлено '.$exportCounts["add"].' элементов.'];
        }
        if ($exportCounts["upd"] > 0){
            $this->model->updItems ($itemsToUpd);
            $_SESSION["message"][]  = ['suc', 'Обновлено '.$exportCounts["upd"].' элементов.'];
        }
        if ($exportCounts["skip"] > 0){
            $_SESSION["message"][]  = ['suc', 'Пропущено '.$exportCounts["skip"].' одинаковых элементов.'];
        }
        if ($delNotFound and $exportCounts["del"] > 0){
            $this->model->delItems ($keysToDel);
            $_SESSION["message"][]  = ['suc', 'Удалено '.$exportCounts["del"].' элементов, отсутствующих в загруженных данных.'];
        }
            
        

        return $html;
    }

    public function modifyImportItem($item)
    {
        if (method_exists(get_class($this->model), 'modifyImportItem')){
            $item = $this->model->modifyImportItem($item);
        }
        return $item;
    }

    /*

    public function transformCurrentItem($item, $actions)
    {
        $cureItem = $item;
        $isChanged = false;

        foreach ($actions as $itemId => $act){
            if (!isset($item[$itemId])) continue;
            
            if ($act == 3){
                $cureItem[$itemId] = $item[$itemId];
            }            
        }

        return $cureItem;
        
    }
        */
    

    public function updateExportItem($item, $newItem, $actions)
    {
        $cureItem = $item;
        $isChanged = false;

        /*
        print_r($cureItem);
        echo '<br>';
        print_r($newItem);
        echo '<hr>';
        */


        foreach ($actions as $itemId => $act){
            

            if (!isset($newItem[$itemId]) or $act < 3 or $cureItem[$itemId] == $newItem[$itemId]) {
                continue;
            }
            if ($cureItem[$itemId] != $newItem[$itemId]){
                /*
                print_r($cureItem[$itemId]);
                echo '<br>';
                print_r($newItem[$itemId]);
                echo '<hr>';
                */
            }
            
            if ($act == 3){
                //print_r($cureItem[$itemId]);
                //echo ' == ';
                //print_r($newItem[$itemId]);
                //echo '<br>';
                $cureItem[$itemId] = $newItem[$itemId];
                $isChanged = true;
            }
            
        }

        if (!$isChanged){
            return Null;
        }

        ksort($cureItem);
        return $cureItem;
        
    }

    

    /* Отображающие функции */


    public function viewFormSurce ()
    {
        $html = '
        <div class="ui secondary menu">
            <a class="active item" data-tab="first">Файлы</a>
            <a class="item" data-tab="second">API</a>
        </div>

        <div class="ui bottom attached active tab segment" data-tab="first">
            <form class="ui form" action="'.$this->url.'?act=import" class="ui form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="form_info[stage]" value="2" />

                <div class="inline fields">
                    <div class="two wide field">
                        <label>Файлы</label>
                    </div>
                    <div class="fourteen wide field">                    
                        <div class="ui left icon input">
                            <i class="file upload icon"></i>
                            <input type="file" name="form_info[files][]" placeholder="" value="" multiple="" required="">
                        </div>
                    </div>                
                </div>

                <div class="three wide field">
                    <button type="submit" class="ui fluid green submit button" name="form_info[type]" value="file">Импорт</button>
                </div>           
            </form>
        </div>

        <div class="ui bottom attached tab segment" data-tab="second">
            <form class="ui form" action="'.$this->url.'?act=import" class="ui form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="form_info[stage]" value="2" />

                <div class="inline fields">
                    <div class="two wide field">
                        <label>Endpoint</label>
                    </div>
                    <div class="fourteen wide field">                    
                        <div class="ui left icon input">
                            <i class="linkify icon"></i>
                            <input type="text" name="form_info[ep]" placeholder="" value="" required="">
                        </div>
                    </div>                
                </div>

                <div class="inline fields">
                    <div class="two wide field">
                        <label>Autorisation</label>
                    </div>
                    <div class="fourteen wide field">                    
                        <div class="ui left icon input">
                            <i class="key icon"></i>
                            <input type="text" name="form_info[key]" placeholder="" value="">
                        </div>
                    </div>
                </div>
                <div class="inline fields">
                    <div class="two wide field">
                        <label>Item container</label>
                    </div>
                    <div class="fourteen wide field">                    
                        <div class="ui left icon input">
                            <i class="database icon"></i>
                            <input type="text" name="form_info[container]" placeholder="" value="">
                        </div>
                    </div>
                </div>

                <div class="three wide field">
                    <button type="submit" class="ui fluid green submit button" name="form_info[type]" value="api" >Импорт</button>
                </div>           
            </form>
        </div>
        ';        
        
        return $html;
    }

    


    protected function viewFormSetup ($schem, $row)
    {
        $primaryType = $this->post["primaryType"] ?? 1;

        $header = ['Поле в базе', 'Колонка из файла'];
        if ($primaryType == 2) $header = ['Колонка из файла', 'Поле в базе'];

        $hidden_fields_html = '';
        foreach ($this->post as $key => $value){
            if (is_array($value) or $key != 'filePath') continue;
            $hidden_fields_html .= '<input type="hidden" name="form_info['.$key.']" value="'.$value.'" />';
        }
        

        $html = '
            <form class="ui form" action="'.$this->url.'?act=import" class="ui form" method="post">
                '.$hidden_fields_html.'
                '.$this->viewFormSurceItems ().'
                <div class="inline fields">
                    <div class="two wide field">'.$header [0].'</div>
                    <div class="fourteen wide field">'.$header [1].'</div>                
                </div>
                '.$this->viewSchemRow ($schem, $row, $primaryType).'
                <div class="field">
                    <a href="'.$this->url.'?act=export" class="ui gray button">Отменить</a>
                    <button type="submit" name="form_info[stage]" value="2" class="ui green submit button">Обновить</button>
                    <button type="submit" name="form_info[stage]" value="3" class="ui green submit button">Продложить</button>
                </div>
            </form>        
        ';
        return $html;
    }


    protected function viewFormSurceItems ()
    {
        $delimiter = $this->post["delimiter"] ?? ";";
        $hasHeader = $this->post["hasHeaders"] ?? false;
        $primaryType = $this->post["primaryType"] ?? 1;        
        $matchAct = $this->post["matchAct"] ?? 1;
        $delNotFound = $this->post["delNotFound"] ?? false;

        /*
        $cureFilePath = $this->post["filePath"] ?? '';
        $relations = $this->post["relations"] ?? [];
        $actions = $this->post["actions"] ?? [];
        */

        $fields = [
            ['delimiter', 'Разделитель', 'select', $delimiter],
            ['hasHeaders', 'Пропускать 1 строку (заголовок)', 'checkbox', $hasHeader],
            ['primaryType', 'Выбор полей', 'radio', $primaryType],
            ['matchAct', 'Если запись уже есть', 'radio', $matchAct],
            ['delNotFound', 'Удалять отсутствующие', 'checkbox', $delNotFound],
        ];

        $html = '';
        foreach ($fields as $fieldInfo){

            $fieldHtml = $lableHtml = '';

            if ($fieldInfo[2] == 'checkbox'){

                $fieldname = 'form_info['.$fieldInfo[0].']';
                $checked = ($fieldInfo[3]) ? ' checked' : '';
                $fieldHtml = '
                <label>
                    <input type="checkbox" name="'.$fieldname.'" placeholder="" '.$checked.'> '.$fieldInfo[1].'
                </label>
                ';

            } else if ($fieldInfo[2] == 'select'){

                $lableHtml = $fieldInfo[1];
                $fieldArr = $fieldInfo[0].'s';
                $fieldname = 'form_info['.$fieldInfo[0].']';
                $fieldHtml = $this->getFormSelect ($this->$fieldArr, $fieldname, $fieldInfo[3]);

            } else if ($fieldInfo[2] == 'radio'){

                $lableHtml = $fieldInfo[1];
                $fieldArr = $fieldInfo[0].'s';
                $fieldname = 'form_info['.$fieldInfo[0].']';
                $fieldHtml = $this->getFormRadio ($this->$fieldArr, $fieldname, $fieldInfo[3]);

            }

            $html .= '
            <div class="inline fields">
                <div class="two wide field">
                    <label>'.$lableHtml.'</label>
                </div>
                <div class="fourteen wide field">
                    '.$fieldHtml.'
                </div>
            </div>';
        }

        return $html;
    }

    protected function viewSchemRow ($schem, $row, $primaryType)
    {
        $matchAct = $this->post["matchAct"] ?? 1;
        $relations = $this->post["relations"] ?? [];
        $actions = $this->post["actions"] ?? [];

        
        $schem_vals = array_filter($schem, fn($item) => $item[4] != 2);        
        $schem_vals =  array_combine(
            array_map(fn($v) => $v[3], $schem_vals), 
            array_map(fn($v) => $v[10] ?? $v[2], $schem_vals)
        );
        

        $row_vals =  array_combine(
            array_keys($row), 
            array_map(fn($k, $v) => $v,  array_keys($row), $row)
        );

        $select = $row_vals;
        $items = $schem_vals;

        if ($primaryType == 2) {
            $select = $schem_vals;
            $select["add"] = "Добавить в новую колонку";
            $items = $row_vals;
        }
        
        $html = '';
        //print_r($actions);
        //echo '<br>';


        foreach ($items as $id => $name){
            $fieldNameRel = 'form_info[relations]['.$id.']';
            $fieldNameAct = 'form_info[actions]['.$id.']';

            $text = $name;
            if ($primaryType == 2) {
                $text .= ' (Колонка ['.($id + 1).'])';
            }

            
            $cureRel = $relations[$id] ?? '';
            $cureAct = $actions[$id] ?? '';
            
            $html .= '
            <div class="inline fields">
                <input type="hidden" name="form_info[items]['.$id.']" value="'.$name.'" />
                <div class="two wide field">
                    '.$text.'
                </div>
                <div class="seven wide field">                    
                    '.$this->getFormSelect ($select, $fieldNameRel, $cureRel).'
                </div> 
                <div class="seven wide field">                    
                    '.$this->getFormSelect ($this->actions, $fieldNameAct, $cureAct).'
                </div>               
            </div>
            ';
        }

        return $html;

    }


    protected function getFormSelect ($items, $fieldName, $cureVal='')
    {
        $opt_html = '<option value="">Не назначено</option>';        
        foreach ($items as $value => $name){
            $selected = '';
            if ($cureVal != '' and $value == $cureVal) $selected = ' selected';
            $opt_html .= '<option value="'.$value.'" '.$selected.'>'.$name.'</option>';
        }

        $html = '
        <select name="'.$fieldName.'">
            '.$opt_html.'
        </select>
        ';
        return $html;
    }

    protected function getFormRadio ($items, $fieldName, $cureVal='')
    {
        $html = '';
        foreach ($items as $value => $name){
            $checked = '';
            if ($cureVal != '' and $value == $cureVal) $checked = ' checked';
            $html .= '
            <label> <input type="radio" name="'.$fieldName.'" value="'.$value.'" '.$checked.'> '.$name.'</label>';
        }

        return $html;
    }
    





    /* Вспомогательные функции */

    protected function readFileCsv ($exportFilePath, $delimiter, $start=0, $end=1000000)
    {
        if (!file_exists($exportFilePath)){
            $_SESSION["message"][]  = ['err', 'Файл для экспорта не найден'];
            return [];
        }

        $file = fopen($exportFilePath, 'r');

        if ($file !== FALSE) {

            $count = 0;
            $dataCount = null;
            $data = [];
            while (($row = fgetcsv($file, 100000, $delimiter)) !== FALSE){
                if (!$dataCount) $dataCount = count($row);

                if ($count >= $end) break;
                if ($count >= $start) {
                    $cureCount = count($row);
                    if ($cureCount == $dataCount) {
                        $data[] = $row;
                    } else {
                        $mess = '
                        Элемент пропущен.
                        Количество полей отличается от нормы ('.count($row).'/'.$dataCount.').
                        Поля элемента: <ol><li>'.implode ("</li><li>", $row).'</li></ol>
                        ';
                        $_SESSION["message"][]  = ['err', $mess];
                    }
                }                
                $count ++;

            }
            fclose($file);
            return $data;

        } else {
            $_SESSION["message"][]  = ['err', 'Не удалось открыть файл.'];
            return [];
        }
    }



    protected function deleteOldFiles($directory, $ageInMinutes = 10) {
        $current_time = time();
        $ageInSeconds = $ageInMinutes * 60;

        foreach (new \DirectoryIterator($directory) as $fileInfo) {
            if ($fileInfo->isDot()) continue;

            if ($fileInfo->isFile() && ($current_time - $fileInfo->getMTime() > $ageInSeconds)) {
                unlink($fileInfo->getRealPath());
            }
        }
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

    protected function flattenArray($array, $prefix = '') {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '_' . $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
}
