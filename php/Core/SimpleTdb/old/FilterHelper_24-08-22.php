<?php

namespace SimpleTdb;


class FilterHelper{

    /*
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
        */

    
    public function filter ($items, $filters)
    {        
        $filterItem = function($item) use ($filters){
            foreach ($filters as $filtInfo) {                
                // Проверяем наличие и валидность необходимых полей
                if (empty($filtInfo["id"]) || empty($filtInfo["type"])) {
                    continue;
                }
    
                $fieldId = $filtInfo["id"];
                $type = $filtInfo["type"];            
                $useCase = $filtInfo[1] ?? false;
                $useAny = $filtInfo[2] ?? false;
    
                $value = $item[$fieldId] ?? '';
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
        };

        $result = array_filter($items, $filterItem);
        
        

        /*
        
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
        */
    }
}
