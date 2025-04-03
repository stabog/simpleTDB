<?php

class FilterHelper{

    protected $typeCond = [
        1 => ['Равно'],
        2 => ['Не равно'],
        3 => ['Больше'],
        4 => ['Больше или равно'],
        5 => ['Меньше'],
        6 => ['Меньше или равно'],
        7 => ['Содержит'],
        8 => ['Не содержит'],
        9 => ['Между'],

    ];

    protected $url = '';
    protected $itemsSchem = [];
    protected $activeFilters = [];

    protected $schemOpt = [];
    protected $typeOpt = [];

    protected $post = [];

    public function __construct($url, $schem, $activeFilters=[]){

        $this->url = $url;
        $this->itemsSchem = $schem;

        foreach ($this->itemsSchem as $sId => $sInfo){
            $this->schemOpt[$sId][0] = $sInfo[10] ?? $sInfo[2];
        }

        foreach ($this->typeCond as $fId => $fInfo){
            $this->typeOpt[$fId][0] = $fInfo[0] ?? '';
        }

        if (count($activeFilters) != 0){

            $this->activeFilters = $activeFilters;

        } else {

            if (isset($_POST["filt"])){
                foreach ($_POST["filt"] as $value){
                    $this->activeFilters[] = $value;
                }
            }
            if (isset($_GET["filt"])){
                foreach ($_GET["filt"] as $value){
                    $this->activeFilters[] = $value;
                }
            }

        }

    }
    

    public function filterItems ($dataItems)
    {        
        if (count($this->activeFilters) == 0) return [];
        
        $items = array_filter ($dataItems, function ($itemInfo) {
            return $this->checkConds ($itemInfo);
        });

        return $items;        
    }

    
    public function checkConds ($itemInfo)
    {        
        foreach ($this->activeFilters as $filtInfo) {
            // Проверяем наличие и валидность необходимых полей
            if (empty($filtInfo["id"]) || empty($filtInfo["type"])) {
                continue;
            }

            $fieldId = $filtInfo["id"];
            $type = $filtInfo["type"];            
            $useCase = $filtInfo[1] ?? false;
            $useAny = $filtInfo[2] ?? false;

            $value = $itemInfo[$fieldId] ?? '';
            $value = $useCase ? $value : mb_strtolower($value, 'UTF-8');            

            $needle = trim($filtInfo["value"]) ?? '';
            
            if ($type == 1 || $type == 2){
                $searchArr = $useAny ? explode(" ", $needle) : [$needle];
                $searchArr = array_diff($searchArr, [""]);

                foreach ($searchArr as $search){
                    if ($value == $search){
                        //Если Обнаружилось равенство то:
                        //Если type = "Равно", переходим к следующему фильтру
                        //Если type = "Не равно", возвращаем ошибку
                        if ($type == 1) continue 2;
                        else return false;
                    }
                }
                // Если равенство не обнаружилось и type = "Не равно", переходим к следующему фильтру
                if ($type == 2) continue;

            } else if ($type == 3){

                //Если значения меньше или равны - возвращаем false
                if ($value <= $needle) return false;
                continue;

            } else if ($type == 4){

                //Если значения строго меньше - возвращаем false
                if ($value < $needle) return false;
                continue;

            } else if ($type == 5){

                //Если значения больше или равно - возвращаем false
                if ($value >= $needle) return false;
                continue;

            } else if ($type == 6){

                //Если значения строго больше - возвращаем false
                if ($value > $needle) return false;
                continue;

            } else if ($type == 7 || $type == 8){
                
                $searchArr = $useAny ? explode(" ", $needle) : [$needle];
                $searchArr = array_diff($searchArr, [""]);

                foreach ($searchArr as $search){
                    $search = $useCase ? $search : mb_strtolower($search, 'UTF-8');           

                    if (strpos ($value, $search) !== false) {
                        //Если Обнаружилось совпадение то:
                        //Если type = "Содержит", переходим к следующему фильтру
                        //Если type = "Не содержит" возвращаем ошибку
                        if ($type == 7) continue 2;
                        else return false;                   
                    }
                }
                // Если совпадения не найдены и type == "Не содержит", переходим к следующему фильтру
                if ($type == 8) continue;
            }

            //Если не сработали фильтры - возвращаем false
            return false;
        }
        
        //Если прошлись по всем условиям - возвращаем true
        return true;
    }




    public function viewFiltForm ()
    {        

        $html = '
        <form class="ui form" action="'.$this->url.'?act=search" method="get" id="searchForm">
            <input type="hidden" name="act" value="search">
            <div id="fieldsContainer">
                '.$this->viewFiltFields ().'
                
            </div>

            <div class="tfield">
                <button type="button" class="ui blue button" id="addFieldButton">Добавить поле</button>
                <button type="submit" class="ui green submit button">Искать</button>
            </div>

        </form>

        <script type="text/javascript" charset="utf-8">
            function createNewFieldHTML(index) {
                return `'.$this->viewFiltField ('${index}').'`;
            }
        </script>';

        return $html;
    }

    public function viewFiltFields ($html = '')
    {
        //print_r($this->activeFilters);

        $count = 0;
        foreach ($this->activeFilters as $filtInfo){

            $count ++;
            $filtId = $count;
            $fieldId = $filtInfo["id"];
            $type = $filtInfo["type"];
            $value = $filtInfo["value"];
            $useCase = $filtInfo[1] ?? false;
            $useAny = $filtInfo[2] ?? false;

            $html .= $this->viewFiltField ($filtId, $type, $fieldId, $value, $useCase, $useAny);
        }
        
        return $html;
        
    }



    public function viewFiltField ($filtId, $type='', $id='', $value='', $useCase=false, $useAny=false)
    {        
        $schemName = 'filt['.$filtId.'][id]';
        $typeName = 'filt['.$filtId.'][type]';
        $typeClasses = ['type-select'];

        $checked[1] = ($useCase)? ' checked' : '';
        $checked[2] = ($useAny)? ' checked' : '';
        
        $hiddenStyle = '';
        $useAnyTypes = [1,2,7,8];
        if (!in_array($type, $useAnyTypes)) $hiddenStyle = ' style="display: none;"';

        
        

        $html = '
        <div class="inline fields" data-index="'.$filtId.'">
            <div class="field">
                '.$this->getFormSelect ($this->schemOpt, $schemName, $id, [], 'Отключен').'
            </div>
            <div class="field">
                '.$this->getFormSelect ($this->typeOpt, $typeName, $type, $typeClasses).'
            </div>
            <div class="field">
                <input type="text" name="filt['.$filtId.'][value]" placeholder="" value="'.$value.'">
            </div>
        
            <div class="fields additional-fields" '.$hiddenStyle.'>
                <div class="field">
                    <label><input type="checkbox" name="filt['.$filtId.'][1]" placeholder="" value="1" '.$checked[1].'> Учитывать регистр</label>                
                </div>
                <div class="field">
                    <label><input type="checkbox" name="filt['.$filtId.'][2]" placeholder="" value="1" '.$checked[2].'> Любое слово</label>                
                </div>
            </div>
            <div class="field">
                <button type="button" class="ui red icon button removeField"><i class="trash alternate icon"></i></button>
            </div>
        </div>';

        
        
        return $html;
    }

    protected function getFormSelect ($items, $fieldName, $cureVal='', $classes=[], $emptyName=null)
    {
        $startOptName = $emptyName ?? 'Не выбрано';

        $opt_html = '<option value="">'.$startOptName.'</option>';
        foreach ($items as $value => $info){
            $selected = '';
            if ($cureVal != '' and $value == $cureVal) $selected = ' selected';

            /*
            $dataType = '';
            if (isset($info[1]) and $info[1] != '') $dataType = 'data-type="'.$info[1].'"';
            */

            $opt_html .= '<option value="'.$value.'" '.$selected.'>'.$info[0].'</option>';
        }

        $html = '
        <select name="'.$fieldName.'" class="'.implode(" ", $classes).'">
            '.$opt_html.'
        </select>
        ';
        return $html;
    }
}
