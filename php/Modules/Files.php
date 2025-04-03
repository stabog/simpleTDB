<?php
namespace FileSearcher;

use SimpleTdb\TextDataBaseModel as TDBM;


class Files extends TDBM {
    protected $dbName = 'files';
    protected $schem = [
        0 => [0, '', 'id', 0, 2, true, 'numb', '', 1, 2, 'Id', '', '', '', 1, '', [], [], []],
        1 => [1, '', 'sys', 1, 2, false, 'arra', '', 1, 2, 'Системные поля', '', '', '', 2, '', [], [], []],
        2 => [2, '', 'path', 2, 1, true, 'text', '', 1, 4, 'Полный путь', '', '', '', 3, '', [], [], []],
        3 => [3, '', 'name', 3, 1, false,  'text', '', 1, 4, 'Имя файла', '', '', '', 4, '', [], [], []],
        4 => [4, '', 'ext', 4, 1, false, 'text', '', 1, 4, 'Расширение файла', '', '', '', 5, '', [], [], []],
        5 => [5, '', 'type', 5, 1, false, 'list', '', 11, 4, 'Тип', '', '', '', 6, '', [], [], []],        
        6 => [6, '', 'size', 6, 1, false, 'numb', 2, 1, 4, 'Размер', '', '', '', 7, '', [], [], []],
        7 => [7, '', 'timeCreate', 7, 1, false, 'time', 0, 4, '', 'Дата создания', '', '', '', 8, '', [], [], []],
        8 => [8, '', 'timeModify', 8, 1, false, 'time', 0, 4, '', 'Дата изменения', '', '', '', 9, '', [], [], []],
        9 => [9, '', 'parrentPath', 9, 1, false, 'text', 1, 4, '', 'Родительская папка', '', '', '', 10, '', [], [], []],
    ];
    

    public function modifyImportItem($item)
    {
        $cureItem = $item;
        $cureItem[6] = $cureItem[6] ?? 0; //Размер
        $cureItem[7] = $cureItem[7] ?? ''; //Дата создания
        $cureItem[8] = $cureItem[8] ?? ''; //Дата изменения
        $dateCreate = str_replace('.', '-', $cureItem[7]);
        $dateModify = str_replace('.', '-', $cureItem[8]);

        $fileName = $fileExt = '';

        $filePath = str_ireplace("I:\\OneDrive - Zest Leaders\\Dropbox\\", "", $cureItem[2]);

        $fileInfo = pathinfo($filePath);
        $extString = $fileInfo['extension'] ?? '';
        //Если в расширении найдены пробелы или размер файла == 0
        if (strpos($extString, ' ') !== false or $cureItem[6] == 0) {
            $fileName = $fileInfo['basename'];            
        } else {
            $fileName = $fileInfo['filename'];
            $fileExt = $extString;
        }

        $rootPath = $fileInfo['dirname']; 
        
        $cureItem[2] = $filePath;
        $cureItem[3] = $fileName; // Получаем имя файла без расширения
        $cureItem[4] = $fileExt; // Получаем расширение файла
        $cureItem[5] = ($cureItem[4] == '' and $cureItem[6] == 0) ? 0 : 1; // Получаем тип файла        
        $cureItem[7] = strtotime($dateCreate);
        $cureItem[8] = strtotime($dateModify);
        $cureItem[9] = $rootPath;

        ksort($cureItem);

        return $cureItem;
    }
}