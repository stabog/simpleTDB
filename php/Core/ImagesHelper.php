<?php

class ImagesHelper{

    public function imgResizeToJpg($src, $dest, &$width, &$height, $quality=75) {
  
        if (!file_exists($src)) return 1; // исходный файл не найден
        $size = getimagesize($src);
        if ($size === false) return 2; // не удалось получить параметры файла
    
        // Определяем исходный формат по MIME-информации и выбираем соответствующую imagecreatefrom-функцию.
        $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
        $icfunc="imagecreatefrom".$format;
        if (!function_exists($icfunc)) return 3; // не существует подходящей функции преобразования
    
        // Определяем необходимость преобразования размера
        if ( $width<$size[0] || $height<$size[1])
        $ratio = min($width/$size[0],$height/$size[1]);
        else
        $ratio=1;
    
        $width=floor($size[0]*$ratio);
        $height=floor($size[1]*$ratio);
        $isrc=$icfunc($src);
        $idest=imagecreatetruecolor($width,$height);    
    
        imagecopyresampled($idest,$isrc,0,0,0,0,$width,$height,$size[0],$size[1]);
        imagejpeg($idest,$dest,$quality);
        chmod($dest,0666);
        imagedestroy($isrc);
        imagedestroy($idest);
        return 0; // успешно
    }
}
