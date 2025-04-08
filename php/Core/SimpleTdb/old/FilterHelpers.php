<?php

namespace SimpleTdb;


class FilterHelpers{

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

    /*
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
    */
    

    public function filterItems ($dataItems, $filters)
    {        
        if (count($filters) == 0) return $dataItems;

        //Группируем фильтры
        foreach ($filters as $fInfo){
            $groupId = $fInfo["group"] ?? 0;
            $groupedFilters[$groupId] = $groupedFilters[$groupId] ?? [];
            $groupedFilters[$groupId][] = $fInfo;
        }
        
        $groupedItems = $items = [];

        foreach ($groupedFilters as $gId => $activeFilters){
            $groupedItems[$gId] = array_filter ($dataItems, function ($itemInfo) use ($activeFilters) {
                return $this->checkConds ($itemInfo, $activeFilters);
            });
        }

        foreach ($groupedItems as $cureItems){
            $items = (count($items) == 0)? $cureItems :  $items + $cureItems;
        }
        

        return $items;        
    }

    
    public function checkConds ($itemInfo, $activeFilters)
    {        
        foreach ($activeFilters as $filtInfo) {
            
            // Проверяем наличие и валидность необходимых полей
            if (empty($filtInfo["id"]) || empty($filtInfo["type"])) {
                continue;
            }

            $fieldId = $filtInfo["id"];
            $type = $filtInfo["type"];            
            $useCase = $filtInfo["useCase"] ?? false;
            $useAny = $filtInfo["useAny"] ?? false;

            $value = $itemInfo[$fieldId] ?? '';
            $needle = $filtInfo["value"] ?? '';

            if (!is_array($value)){
                $value = $useCase ? $value : mb_strtolower($value, 'UTF-8');
            }
            
            if (!is_array($needle)){
                $needle = trim($filtInfo["value"]);
            }

            
            
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
                
                $contain = $this->checkContain ($needle, $value, $type, $useAny=false, $useCase=false);
                if ($contain){
                    //Если type = "Содержит", переходим к следующему фильтру
                    if ($type == 7) continue;
                    else return false;
                } else {
                    //Если type = "Содержит", возвращаем false
                    if ($type == 7) return false;
                    else continue;
                }

                /*
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
                */
            }

            //Если не сработали фильтры - возвращаем false
            return false;
        }
        
        //Если прошлись по всем условиям - возвращаем true
        return true;
    }


    protected function checkContain ($needle, $value, $type, $useAny=false, $useCase=false)
    {
        
        
        if (!is_array($needle)){
            $searchArr = $useAny ? explode(" ", $needle) : [$needle];
        } else {
            $searchArr = $needle;
        }
        
        $searchArr = array_diff($searchArr, [""]);

        foreach ($searchArr as $search){
            $search = $useCase ? $search : mb_strtolower($search, 'UTF-8');
            
            //echo $search.': ';
            //print_r($value);
            //echo '<br>';

            //Добавить обработку массива $searchArr если $useAny=false

            if (is_array($value)){
                if (in_array($search, $value)) {
                    return true;
                }
            } else {
                if (strpos ($value, $search) !== false) {
                    return true;
                }
            }

            
        }
        
        return false;
    }


}

