<?php

class UploadFilesHelper{
    protected $path = 'files/';
    protected $test = false;
	protected $hasFiles = false;

    protected $uploadItems = [];
    protected $uploadIds = [];

    protected $postFiles = [];

    protected $imgMWidth = 1000;
    protected $imgMHeight = 1000;
    protected $imgMpath = 'm/';

    protected $imgSWidth = 200;
    protected $imgSHeight = 200;
    protected $imgSpath = 's/';

    protected $maxFileSize = 104857600;
    protected $allowedTypes = [
        'image' => ['image/jpeg', 'image/png'],
		'json' => ['text/plain'],
		'opus' => ['audio/ogg'],
		
    ];
	
	/*
	'pdf' => ['application/pdf'],
	'doc' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
	'pres' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
	'tabl' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
	'image' => ['image/jpeg', 'image/png'],
	'arch' => ['application/zip'],
	'json' => ['text/plain'],
	'opus' => ['audio/ogg'],
	*/

    public function __construct($path='', $postFiles=[]){        
        $this->postFiles = $postFiles;
        
        if ($path != '') $this->path = $path;
        if (!file_exists($this->path)) mkdir($this->path, 0777, true );
        
        $this->imgMpath = $this->path.$this->imgMpath;
        $this->imgSpath = $this->path.$this->imgSpath;

        
        
        $this->checkFilesUpload($this->postFiles);		
		if (!$this->hasFiles) $_SESSION["message"][] = ['err', 'Файлы не выбраны.'];
        

    }

    public function checkFilesUpload () {        
        $has_files = false;
        
        if(empty($this->postFiles)) return false;

        //if(empty($_FILES)) return false;
        //$files = $_FILES['form_info'];

        foreach ($this->postFiles['tmp_name'] as $field_id => $tmp_names){
            foreach ($tmp_names as $key => $temp_name){
                if( !empty($temp_name) && is_uploaded_file($temp_name)){
                    // found one!
                    $has_files = true;
                }
            }
        }
        
        $this->hasFiles =  $has_files;
        return $this->hasFiles;
    }

    public function uploadFiles($save_names=false, $form_params=[]){
        //$form_params = ["json" => [false, "text"], "opus" => [false, "audio"]];        
        
        
        $files = $_FILES['form_info'];

        foreach ($files['tmp_name'] as $field_id => $tmp_names){
            foreach ($tmp_names as $key => $tmp_name){
				//Сохраняем оригинальное имя
				$save_names_cure = $save_names;
				//Задаем имя вручную через параметры
				$force_name_cure = false;
				
				if (isset($form_params[$field_id])){					
					$save_names_cure = $form_params[$field_id][0];
					$force_name_cure = $form_params[$field_id][1];
				}
				
				if ($files["name"][$field_id][$key] == '') continue;
				
                $old_name = $files["name"][$field_id][$key];				
                
                //Проверка на размер
                if ($files["size"][$field_id][$key] > $this->maxFileSize){
                    $_SESSION["message"][] = ['err', 'Файл <b>'.$old_name.'</b> не загружен. Размер файла должен быть < 100 Мб'];
                    continue;
                }

                //Проверка на допустимый тип
                $check_mode = '';
                if ($this->test) $check_mode = 'text';

                $file_type = $this->checkFileType ($tmp_name, $check_mode);
                if (!$file_type) {
                    $_SESSION["message"][] = ['err', 'Файл <b>'.$old_name.'</b> не загружен. Недопустимый тип файла'];
                    continue;
                }                

                $file_size = $files["size"][$field_id][$key];

                $ras_arr = explode(".", $old_name);
                $ras = array_pop ($ras_arr);
                $old_name_text = implode(' ', $ras_arr);
				
				   			
                $new_name = md5(date("Y-m-d-h-s").rand(100, 1000)).'.'.$ras;
				if ($save_names_cure) $new_name = $old_name;
				if ($force_name_cure != false) $new_name = $force_name_cure.'.'.$ras;				
				 
                $new_name_path = $this->path.$new_name;

                if ($this->test) {
                    $mess_text = '
                    Имя файла: '.$old_name_text.'<br>
                    Расширение: '.$ras.'<br>
                    Тип: '.$file_type.'<br>
                    Размер: '.$file_size.'<br>
                    Новый путь: '.$new_name_path;

                    $_SESSION["message"][] = ['suc', $mess_text];
                    
                }

                if ($file_type == 'image') {
                    if (!file_exists($this->imgMpath)) mkdir($this->imgMpath, 0777, true );
                    if (!file_exists($this->imgSpath)) mkdir($this->imgSpath, 0777, true );
                    
                    $img_s_path = $this->imgSpath.$new_name;
					if (file_exists($img_s_path)) unlink($img_s_path);
                    $conv_m = $this->imgResizeToJpg ($tmp_name, $img_s_path, $this->imgSWidth, $this->imgSHeight);

                    $img_m_path = $this->imgMpath.$new_name;
					if (file_exists($img_m_path)) unlink($img_m_path);
                    $conv_m = $this->imgResizeToJpg ($tmp_name, $img_m_path, $this->imgMWidth, $this->imgMHeight);

                    $file_info[3][0] = $img_s_path;
                    $file_info[3][1] = $img_m_path;

                }

            	
				if (file_exists($new_name_path)) unlink($new_name_path);
                if (move_uploaded_file($tmp_name, $new_name_path)) {
                    
                    $file_info = [
                        "name" => $new_name,
                        "path" => $new_name_path,
                    ];

                    /*
                    $file_info[0] = ''; //id в базе
                    $file_info[1] = ''; //данные по дате / пользователю
                    $file_info[2][0] = $new_name_path;
                    $file_info[2][1] = $old_name;
                    $file_info[2][2] = $file_type;
                    $file_info[2][3] = $file_size;
                    */

                    $this->uploadItems[$field_id][] = $file_info;
                    unset($file_info);                    

                    $_SESSION["message"][] = ['suc', 'Файл <b>'.$old_name.'</b> успешно загружен.'];
                }
            }
        }

        if (count($this->uploadItems) > 0) return $this->uploadItems;
    }
    
    
    //Переформировываем массив в FieldName => FileId => SysKey => Value
    public function setFormFilesList (){
        $files = $_FILES['form_info'];
        
        //Переформировываем массив в FieldName => FileId => SysKey => Value
        foreach ($files as $key=>$val) {                
            foreach ($val as $fild_name => $info){                    
                foreach ($info as $file_id => $file_val){
                    $postFiles[$fild_name][$file_id][$key] = $file_val;
                }
            }                
        }

        //Убираем файлы, загруженные с ошибками
        foreach ($postFiles as $fild_name => $files){
            foreach ($files as $f_id => $f_vals){
                if ($f_vals["error"] > 0) unset ($postFiles[$fild_name][$f_id]);
            }
        }

        //Чистим поля, в которых нет файлов
        foreach ($postFiles as $fild_name => $files){                
            if (count($files) == 0) unset ($postFiles[$fild_name]);
        }
        
        return $postFiles;
    }


    protected function checkFileType ($file, $check='') {
        
        $finfo = new finfo();
        $file_mime_type = $finfo->file($file, FILEINFO_MIME_TYPE);

		//echo $file_mime_type.'<br>';
        foreach ($this->allowedTypes as $type => $type_vars){
            if (in_array($file_mime_type, $type_vars)) return $type;
        }

        if ($check != '') return $file_mime_type;
        return false;
        
    }

    protected function imgResizeToJpg($src, $dest, &$width, &$height, $quality=75) {
  
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

/*
print_r($files);
if($files['error'] > 0) {
//в зависимости от номера ошибки выводим соответствующее сообщение
//UPLOAD_MAX_FILE_SIZE - значение установленное в php.ini
//MAX_FILE_SIZE значение указанное в html-форме загрузки файла
switch ($files['error'])
{
case 1: echo 'Размер файла превышает допустимое значение UPLOAD_MAX_FILE_SIZE'; break;
case 2: echo 'Размер файла превышает допустимое значение MAX_FILE_SIZE'; break;
case 3: echo 'Не удалось загрузить часть файла'; break;
case 4: echo 'Файл не был загружен'; break;
case 6: echo 'Отсутствует временная папка.'; break;
case 7: echo 'Не удалось записать файл на диск.'; break;
case 8: echo 'PHP-расширение остановило загрузку файла.'; break;
}
exit;
}
*/


/*
//if (isset($_FILES['files']) and is_array($_FILES['files'])) $files = $_FILES['files'];

foreach ($files['tmp_name'] as $key => $tmp_name){
$old_name  = $files["name"] [$key] ;

if ($files["size"][$key] > 104857600){
    $_SESSION["message"][] = ['err', 'Файл <b>'.$old_name.'</b> не загружен. Размер файла должен быть < 100 Мб'];
    continue;
}

if ($files["type"][$key] != "audio/ogg"){
    $_SESSION["message"][] = ['err', 'Файл <b>'.$old_name.'</b> не загружен. Формат файла должен быть ".opus"'];
    continue;
}

$ras_arr = explode(".", $old_name);
$ras = array_pop ($ras_arr);
$old_name = implode(' ', $ras_arr);

$new_name = md5(date("Y-m-d-h-s").rand(100, 1000)).'.'.$ras;
$new_name_path = $local_path.$new_name;



if (move_uploaded_file($files["tmp_name"] [$key], $new_name_path)) {
    
    
    //$result [$key] ["type"]  = $files["type"] [$key] ;
    //$result [$key] ["size"]  = $files["size"] [$key] ;
    //$result [$key] ["tmp_name"]  = $files["tmp_name"] [$key] ;
    

    $file = $local_path.$new_name;
    $file_info = (new FFmpegRun)->ffmpegInfo ($file) ;

    //print_r($file_info);
    $dur_arr = explode(".", $file_info ["dur"]);
    $file_info ["dur"] = $dur_arr[0];
    $dur_arr = explode(":", $dur_arr[0]);
    $dur_secs = $dur_arr[0] * 3600 + $dur_arr[1] * 60 + round($dur_arr[1], 0);

    $form_info[0] = '';
    $form_info[1][0] = time();
    $form_info[1][1] = $this->currUserId;
    $form_info[1][2] = time();
    $form_info[1][3] = $this->currUserId;
    $form_info[2] = $this->currUserId;
    $form_info[3] = $proj_id;
    $form_info[4] = $old_name;
    $form_info[5][0] = $new_name;
    $form_info[5][1] = $local_path;
    $form_info[5][2] = $files["size"] [$key];
    $form_info[5][3] = $files["type"] [$key];
    $form_info[5][4] = $file_info ["dur"];
    $form_info[5][5] = $dur_secs;
    $form_info[5][6] = $file_info ["chanels"];
    $form_info[5][7] = $file_info ["bitrate"];
    $form_info[6] = 1;
    $form_info[7][0] = '';
    

    $this->id = $this->data->add($form_info);
    $_SESSION["message"][] = ['suc', 'Файл <b>'.$old_name.'</b> успешно загружен.'];
    
    
}
else {
    $_SESSION["message"][] = ['err', 'Файл <b>'.$old_name.'</b> не загружен. Произошла ошибка при загрузке'];
}



}


//if (isset($result)) print_r($result);

$template = ($mess != '') ? '<div class="ui warning message">'.$mess.'</div>' : '';
return $template;
*/