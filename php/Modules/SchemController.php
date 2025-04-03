<?php
namespace FileSearcher;

use SimpleTdb\TextDataImport as TDI;
use SimpleTdb\TextDataSchem as TDS;

use \FilterHelper as FiltH;

class BaseController{
    protected $noRedirectActs = ['process', 'search', 'view', 'import', 'schem'];
    protected $url = '';
    
    protected $models;
    protected $cureModel;

    protected $post;
    protected $postFiles;

    protected $activeFilters = [];
    protected $sort = [];

    protected $filterHelper;
    protected $import;

    /*
    
    

    protected $curUserId = null;
    protected $files = [];
    protected $items = [];    
    
    */

    public function __construct($models, $cureModelId, $cureModelName){
        $this->models = $models;
        $this->cureModel = $this->models[$cureModelId];

        $this->url = '/bases/'.$cureModelName.'/';

        

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["form_info"])){
            $this->post = $_POST["form_info"];
        }

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
        

        
        $this->filterHelper = new FiltH ($this->url, $this->cureModel->getSchem(), $this->activeFilters);                
        $this->import = new TDI($this->cureModel, $this->url);
        
    }

    public function run ($act, $id, $props, $html='')
    {
        $url = $this->url;
        //$html .= $this->viewMenu ();

        if ($act == '') $act = 'view';

        if ($act == "view") {

            $html .= $this->view ();

        } else if ($act == "import"){ 

            $html .= $this->import->viewImport();

        } else if ($act == "schem"){ 

            $html .= 'Редактирование схемы';
            
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
 

    protected function view ($html='')
    {        
        if (count($this->cureModel->all()) == 0) {            
            $html .= '<div class="ui warning message">Элементы не добавлены</div>';
        }
        
        $items = $this->cureModel->all();
        //$sortedItems = $this->sortItems($this->cureModel->all());
        //$items = array_slice($sortedItems, 0, 1000);
        
        $html .=  $this->viewMenu ();
        $html .=  $this->viewItemsTable ($items);
        return $html;
    }

    

    protected function viewItemsTable ($items)
    {
        

        //$props["url"] = $this->getFullUrl ();
        //$props["sort"] = $this->sort[0] ?? [];

        $props = [];

        [$cols, $rows] = (new TDS())->prepareTableData($this->cureModel->getSchem(), $items);        
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



    
    /*
    protected function viewSearchResults ()
    {
        
        
        $breakAfter = 1000;
        $count = 0;

        //filterItems ($dataItems)
        $filteredItems = $this->filterHelper->filterItems($this->files->all());

        if (count($filteredItems) == 0){            
            return '<div class="ui message">Элементы не найдены</div>';
        }
        //$html =  '<div class="ui message">Найдено '.count($filteredItems).' элементов</div>';

        
        $sortedItems = $this->sortItems($filteredItems);
        $html =  $this->viewItemsTable ($sortedItems);

        return $html;
        
    }

    

    protected function viewItemsList ($items)
    {
        $url_begin = 'https://cloud.zestleaders.com/index.php/apps/files/?dir=/zest/';
        $html = $html_items = '';

        foreach ($items as $itemId => $itemInfo){
            $pathRoot = $itemInfo[9];
            $normPathRoot = str_replace('\\', '/', $pathRoot);
            $folders[$normPathRoot][] = $itemId;
        }
        ksort($folders);

        foreach ($folders as $pathRoot => $itemIsd){
            $html_items .= '
            <h4>                
                '.$pathRoot.'
                <a href="'.$url_begin . rawurlencode($pathRoot).'" target="blank"><i class="external alternate icon"></i></a>
            </h4>';

            foreach ($itemIsd as $itemId){
                $itemInfo = $items[$itemId];
                $name = $itemInfo[3];
                if ($itemInfo[4] != '') $name .= '.'.$itemInfo[4];

                $html_items .= '<p>'.$name.'</p>';
            }

        }

        $html = '        
        <ol>'.$html_items.'</ol>
        ';        

        return $html;
    }

    

    

    


    protected function sortItems($items)
    {
        if (!isset($this->sort) or count($this->sort) == 0){
            return $items;
        }
        
        $sort = $this->sort[0];
        $id = $sort["id"];
        $order = $sort["ord"];

        uasort($items, function ($a, $b) use ($id, $order) {
            $frst = $a[$id];
            $scnd = $b[$id];
            
            if ($order == 0) {
                return $frst <=> $scnd;
            }
            else {
                return $scnd <=> $frst;
            }

        });

        return $items;
    }





    protected  function getFullUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];
        return $protocol . $host . $requestUri;
    }
        */

}