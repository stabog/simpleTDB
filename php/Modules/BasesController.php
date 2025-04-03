<?php
namespace FileSearcher;

use SimpleTdb\TextDataBase as TDB;
use SimpleTdb\TextDataBaseModel as TDBM;

class BasesController{
    protected $url = '/bases/';
    protected $noRedirectActs = ['view', 'add', 'import'];
    
    protected $bases;
    protected $cureBase;

    public $cureModelName;
    public $cureModelId;

    public function __construct($bases, $links=[]){
        
        $this->bases = $bases;        
        $this->cureModelName = (isset($links[1]) and $links[1] != '')? $links[1] : current($this->bases->all())[2];

        foreach ($this->bases->all() as $bId => $bInfo){
            if ($bInfo[2] == $this->cureModelName){
                $this->cureModelId = $bId;
            }        
        }
    }


    public function run ($act, $id, $props, $html='')
    {
        $url = $this->url;

        if ($act == '') $act = 'view';
        
        
        if ($act == "view") {
            $html .= $this->viewMenu ();
        }

        if ($act != '' and !in_array($act, $this->noRedirectActs)) {
            header("Location: $url");
            die();
        }        
        return $html;
        
    } 


    public function getModels ($links)
    {
        $namespace = 'FileSearcher\\';
        //$namespace = '';

        foreach ($this->bases->all() as $bId => $bInfo){
            $baseName = $bInfo[2];            
            $className = $namespace.ucfirst($baseName);

            if (class_exists($className)) {
                //echo $className.'<br>';
                $$baseName = new $className($baseName);
            } else {
                $$baseName = new TDBM($baseName, '', 'guid');
            }                
    
            $models[$bId] = $$baseName;                  
        }

        //print_r(count($models));

        return $models;
    }

    

    

    

    public function viewMenu ()
    {
        $items_html = '';        
        foreach ($this->bases->all() as $bId => $bInfo){
            $itemClass = '';
            if ($bInfo[2] == $this->cureModelName){
                $itemClass .= ' active';
            }

            $items_html .= '<a class="item '.$itemClass.'" href="'.$this->url.$bInfo[2].'/">'.$bInfo[2].'</a>';
        }
        
        $html = '
        <div class="ui tabular  menu">
            '.$items_html.'
            <a class="item" href="'.$this->url.'?act=add"><i class="plus icon"></i></a>
        </div>';        

        return $html;
    }

}