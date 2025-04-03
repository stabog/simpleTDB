<?php
namespace FileSearcher;

use SimpleTdb\TextDataImport as TDI;
use SimpleTdb\TextDataForm as TDF;
use \FilterHelper as FiltH;

class HomeWorksController{
    protected $noRedirectActs = ['view_all', 'manage', 'add', 'edit', 'view', 'eval', 'save'];
    protected $url = '';
    
    protected $models;
    protected $cureModelName;
    protected $cureModel;

    protected $form;

    protected $cureUserId;

    protected $programs;
    protected $users;
    protected $tasks;
    protected $homeworks;

    protected $post;

    /*
    protected $postFiles;

    protected $activeFilters = [];
    protected $sort = [];

    protected $filterHelper;
    protected $import;
    
    */

    

    public function __construct($models, $cureModelId, $cureModelName){
        $this->models = $models;
        $this->cureModelName = $cureModelName;
        $this->cureModel = $this->models[$cureModelId];
        $this->cureModel->setModels($models, $cureModelName);        

        $this->url = '/'.$cureModelName.'/';

        $this->cureUserId = $_SESSION["curUserId"];
        

        

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["form_info"])){
            $this->post = $_POST["form_info"];
        }

        /*

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES["form_info"])){
            $this->postFiles = $_FILES["form_info"];
        }        

        
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (isset($_POST["filt"])) $this->activeFilters = $_POST["filt"];
            if (isset($_POST["sort"])) $this->sort = $_POST["sort"];
        } 

        if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            if (isset($_GET["filt"])) $this->activeFilters = $_GET["filt"];
            if (isset($_GET["sort"])) $this->sort = $_GET["sort"];
        }
        */
        

        //$this->schem = new TDS($this->cureModelName, $this->cureModel->getSchem()); 
        //$this->filterHelper = new FiltH ($this->url, $this->cureModel->schem->getSchem(), $this->activeFilters);                
        //$this->import = new TDI($this->cureModel, $this->url);
        $this->form = new TDF($this->url, $this->cureModel->schem->getSchem('form'), $this->cureModelName);
        
    }

    public function run ($act, $id, $props, $html='')
    {
        $url = $this->url;
        //$html .= $this->viewMenu ();

        $info = [];
        if ($id != ''){
            $info = $this->cureModel->get($id);
        }

        if ($act == '') $act = 'view_all';

        if ($act == "view_all") {

            $html .= $this->view ();

        } else if ($act == "manage"){
            $html .= $this->viewManage ();

        } else if ($act == "add"){
            $html .= $this->form ($props);

        } else if ($act == "edit"){
            $html .= $this->form ($props, $info);

        } else if ($act == "save"){
            $html .= $this->saveItem ();

        }

        if (!in_array($act, $this->noRedirectActs)) {
            header("Location: $url");
            die();
        }        
        return $html;
        
    }

    


    protected function form ($props, $info=[])
    {
        //print_r($info);
        //print_r($this->cureModel->form);

        if (count($info) == 0){
            $info[2] = $props["taskId"] ?? '';
            $info[3] = $props["userId"] ?? '';
        }
        

        $prepInfo = $this->cureModel->schem->prepInfoSchem ($info, 'form');

        $form = $this->cureModel->form;
        $form->setFieldsFromSchem($this->cureModel->schem->getSchem('form'), $prepInfo);
        $form->setFieldsOrder([1,2,3,4,5,6]);
        $form->hideFields([3,4]);
        
        $url = $this->url.'save/';
        return $this->cureModel->form->viewForm($url);
    }


    protected function saveItem ()
    {
        //print_r($this->post);

        $info = $this->post;
        $tobase = $this->cureModel->schem->prepInfoSchem ($info, 'post');

        //Если добавляем
        if ($tobase[0] == ''){
            $newId = $this->cureModel->add($tobase);
            $_SESSION["message"][]  = ['suc', 'Элемент с id ['.$newId.'] добавлен'];
            return $newId;
        }

        //Если обновляем
        //Проверяем на существование
        $newId = $tobase[0];
        $cureItem = $this->cureModel->get($tobase[0]);
        if (!$cureItem) {
            $_SESSION["message"][]  = ['err', 'Элемент с id ['.$newId.'] не найден'];
            return false;
        }

        //Обновляем текущий элемент
        $this->cureModel->upd($newId, $tobase);
        $_SESSION["message"][]  = ['suc', 'Элемент с id ['.$newId.'] обновлен'];

    }



    protected function view()
    {
        return $this->viewPrograms ("programs");
    }


    protected function viewPrograms ($modelName, $parrantInfo=[], $html='')
    {
        $programs = $this->cureModel->schem->models[$modelName];
        $filt["programs"] = [
            ["id" => 12, "type" => 7, "value" => $this->cureUserId, "group" => 1],
            ["id" => 13, "type" => 7, "value" => $this->cureUserId, "group" => 2],
        ];
        $pItems = $programs->all($filt["programs"]);

        foreach ($pItems as $pId => $pInfo){
            $pInfo = $programs->schem->prepInfoSchem ($pInfo, 'form');
            
            $html .= '
            <div>
                <h3>'.$pInfo[2].'</h3>
                '.$this->viewTasks ("tasks", $pInfo).'
            </div>';
        }

        return $html;
    }

    protected function viewTasks ($modelName, $pInfo, $html='')
    {
        $tasks = $this->cureModel->schem->models[$modelName];
        $cureProgId = $pInfo[0];
        $filt["tasks"] = [
            ["id" => 5, "type" => 7, "value" => $cureProgId, "useAny" => true],
        ];
        $tItems = $tasks->all($filt["tasks"]);
        
        foreach ($tItems as $tId => $tInfo){
            $tInfo = $tasks->schem->prepInfoSchem ($tInfo, 'form');
            $html .= '
            <div class="ui segment">
                <div><b>'.$tInfo[2].'</b></div>
                <div class="ui label"><i class="calendar alternate outline icon"></i>'.$tInfo[8].'</div>
                <div>'.$tInfo[3].'</div>
                '.$this->viewTasksButtons ("homeworks", $tInfo).'
            </div>';
        }

        $html = '
        <div class="ui segments">
        '.$html.'
        </div>';

        return $html;
    }

    protected function viewTasksButtons ($modelName, $tInfo, $html='')
    {
        $this->homeworks = $this->cureModel->schem->models[$modelName];
        $filt["homeworks"] = [
            ["id" => 2, "type" => 7, "value" => $tInfo[0], "useAny" => true],
            ["id" => 3, "type" => 7, "value" => $this->cureUserId, "useAny" => true],
        ];
        $homeworks = $this->homeworks->all($filt["homeworks"]);
        $firstItemId = current($homeworks)[0] ?? '';        

        $buttonAdd = (count($homeworks) == 0) ? '<a class="ui button" href="'.$this->url.'add/?props[userId]='.$this->cureUserId.'&props[taskId]='.$tInfo[0].'">Добавить</a>' : '';
        $buttonEdit = (count($homeworks) > 0) ? '<a class="ui button" href="'.$this->url.'edit/?id='.$firstItemId.'">Изменить</a>' : '';
        $buttonView = (count($homeworks) > 0) ? '<a class="ui button" href="'.$this->url.'view/?id='.$firstItemId.'">Просмотреть</a>' : '';
        
        //$buttonEval = (count($homeworks) > 0) ? '<a class="ui button" href="'.$this->url.'eval/?id='.$tInfo[0].'">Оценить других</a>' : '';
        $buttonEval = '';

        $html .= '
            <div class="ui buttons">
                '.$buttonAdd.'
                '.$buttonEdit.'
                '.$buttonView.'
                '.$buttonEval.'
            </div>';

        return $html;
    }

    protected function viewManage()
    {
        return $this->viewProgramsManage ("programs");
    }


    protected function viewProgramsManage ($modelName, $parrantInfo=[], $html='')
    {
        $programs = $this->cureModel->schem->models[$modelName];
        $pItems = $programs->all();

        foreach ($pItems as $pId => $pInfo){
            $html .= '
            <div>
                <h3 class="ui center aligned header">'.$pInfo[2].'</h3>
                '.$this->viewTasksManage ("tasks", $pInfo).'
            </div>';
        }

        return $html;
    }

    protected function viewTasksManage ($modelName, $pInfo, $html='')
    {
        $tasks = $this->cureModel->schem->models[$modelName];
        $cureProgId = $pInfo[0];
        $filt["tasks"] = [
            ["id" => 5, "type" => 7, "value" => $cureProgId, "useAny" => true],
        ];
        $tItems = $tasks->all($filt["tasks"]);
        
        foreach ($tItems as $tId => $tInfo){
            $html .= '
            <div class="ui segment">
                <div><b>'.$tInfo[2].'</b></div>
                <div class="ui label"><i class="calendar alternate outline icon"></i>'.$tInfo[8].'</div>
                <div>'.$tInfo[3].'</div>
            </div>';
        }

        $html = '
        <div class="ui segments">
        '.$html.'
        </div>';

        return $html;
    }

}