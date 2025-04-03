<?php
namespace FileSearcher;

use SimpleTdb\TextDataImport as TDI;
use SimpleTdb\TextDataForm as TDF;
use \FilterHelper as FiltH;

class BaseController{
    protected $noRedirectActs = ['process', 'search', 'view', 'import', 'schem', 'form', 'save', 'del'];
    protected $url = '';
    protected $urlRoot = '/bases/';
    
    protected $models;
    protected $cureModelName;
    protected $cureModel;

    protected $post;
    protected $postFiles;

    protected $activeFilters = [];
    protected $sort = [];

    protected $filterHelper;
    protected $import;
    protected $form;

    

    /*
    
    

    protected $curUserId = null;
    protected $files = [];
    protected $items = [];    
    
    */

    public function __construct($models, $cureModelName){
        $this->models = $models;
        $this->cureModelName = $cureModelName;
        $this->cureModel = $this->models[$cureModelName];
        //$this->cureModel->setModels($models, $cureModelName);        

        $this->url = $this->urlRoot.$cureModelName.'/';
        

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["form_info"])){
            $this->post = $_POST["form_info"];
        }

        /*
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
        $this->filterHelper = new FiltH ($this->url, $this->cureModel->schem->getSchem(), $this->activeFilters);                
        $this->import = new TDI($this->cureModel, $this->url);
        //$this->form = new TDF($this->url, $this->cureModel->schem->getSchem('form'), $this->cureModelName);
        
    }

    public function run ($act, $id, $props, $html='')
    {
        $url = $this->url;
        //$html .= $this->viewMenu ();

        $info = [];
        if ($id != ''){
            $info = $this->cureModel->get($id);
        }

        if ($act == '') $act = 'view';

        if ($act == "view") {

            $html .= $this->view ();

        } else if ($act == "import"){ 

            $html .= $this->import->viewImport();

        } else if ($act == "schem"){
            
            $html .= $this->cureModel->schem->viewScheme();
            
        } else if ($act == "form"){

            $html .= $this->viewForm ('save', $info);

        } else if ($act == "save"){

            $newId = $this->saveItem ();
            $url = $this->url.'?act=form&id='.$newId;        

        } else if ($act == "del"){

            $result = $this->cureModel->del($id);

        }
        /*

        } else if ($act == "search") {

            $html .= $this->viewSearchResults ();

        } else if ($act == "view") {
            $html .= $this->view ();
        }
        */

        if (!in_array($act, $this->noRedirectActs)) {
            header("Location: $url");
            die();
        }        
        return $html;
        
    }

    protected function saveItem ()
    {               
        $tobase = $this->post;
        print_r($tobase);
        echo '<br>';
        
        //Проверяем данные по схеме
        $tobase = $this->cureModel->schem->prepInfoSchem ($tobase, 'post');
        print_r($tobase);

        /*
        foreach ($this->cureModel->schem->getSchem() as $sId => $sInfo){
            $iId = $sInfo[3];
            $iType = $sInfo[6];
            $value = $tobase[$iId] ?? '';
            
            if ($iType == 'time'){
                $value = strtotime($value);
            }            

            $tobase[$iId] = $value;
        }
        */

        $cureId = $tobase[0] ?? '';

        //Если добавляем
        if ($cureId == ''){
            $cureId = $this->cureModel->add($tobase);
            $_SESSION["message"][]  = ['suc', 'Элемент с id ['.$cureId.'] добавлен'];
            return $cureId;
        }

        //Если обновляем
        //Проверяем на существование
        $cureItem = $this->cureModel->get($cureId);
        if (!$cureItem) {
            $_SESSION["message"][]  = ['err', 'Элемент с id ['.$cureId.'] не найден'];
            return false;
        }

        //Обновляем текущий элемент
        $this->cureModel->upd($cureId, $tobase);
        $_SESSION["message"][]  = ['suc', 'Элемент с id ['.$cureId.'] обновлен'];

        return $cureId;
    }
 

    protected function view ($html='')
    {        
        $html .=  $this->viewMenu ();
        if (count($this->cureModel->all()) == 0) {            
            $html .= '<div class="ui warning message">Элементы не добавлены</div>';
            return $html;
        }
        
        $items = $this->cureModel->all();
        //$sortedItems = $this->sortItems($this->cureModel->all());
        //$items = array_slice($sortedItems, 0, 1000);        
        
        $html .=  $this->viewItemsTable ($items);
        return $html;
    }

    protected function viewForm ($act='', $info=[])
    {
        
        /*
        $formAct = ($act == '') ? 'save' : $act;
        return $this->form->viewForm($formAct, $info);
        */

        $prepInfo = $this->cureModel->schem->prepInfoSchem ($info, 'form');

        $form = $this->cureModel->form;
        $form->setFieldsFromSchem($this->cureModel->schem->getSchem('form'), $prepInfo);
        //$form->setFieldsOrder([1,2,3,4,5,6]);
        //$form->hideFields([3,4]);
        
        $url = $this->url.'?act=save';
        return $this->cureModel->form->viewForm($url);
    }

    

    protected function viewItemsTable ($items)
    {
        

        //$props["url"] = $this->getFullUrl ();
        //$props["sort"] = $this->sort[0] ?? [];

        $props = [];

        //[$cols, $rows] = (new TDS($this->cureModelName, $this->cureModel->getSchem()))->prepareTableData($this->cureModel->getSchem(), $items);
        [$cols, $rows] = $this->cureModel->schem->prepareTableData($items);

        $table = new \TableHelper($cols, $rows, $props);
        return $table->viewTable();
    }

    protected function viewMenu ()
    {
        $html = '        
        <div class="ui secondary menu menu-actions">
            <a class="item showHide" href="#filters">Фильтры</a>            
            <a class="item" href="'.$this->url.'?act=import">Импорт</a>
            <a class="item" href="'.$this->url.'?act=schem">Схема</a>
            <div class="hidden" id="filters">
                '.$this->filterHelper->viewFiltForm ().'
            </div>
        </div>';
        

        return $html;
    }




    public function viewRootMenu ()
    {
        $items_html = '';        
        foreach ($this->models as $baseName => $model){
            $itemClass = '';
            if ($baseName == $this->cureModelName){
                $itemClass .= ' active';
            }

            $items_html .= '<a class="item '.$itemClass.'" href="'.$this->urlRoot.$baseName.'/">'.$baseName.'</a>';
        }
        
        $html = '
        <div class="ui tabular menu">
            '.$items_html.'
            <a class="item" href="'.$this->urlRoot.'?act=add"><i class="plus icon"></i></a>
        </div>';        

        return $html;
    }
}