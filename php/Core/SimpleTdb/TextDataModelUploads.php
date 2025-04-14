<?php

namespace SimpleTdb;

use SimpleTdb\TextDataModel as TDM;
use getID3;

class TextDataModelUploads extends TDM {
    protected $dbName = 'uploads';
    protected $indexType = "guid";
    protected $uploadDir = 'uploads/';
    protected $imgMediumDir = '';
    protected $imgSmallDir = '';

    protected $imgOrigSize = 2500;
    protected $imgMediumSize = 1500;
    protected $imgSmallSize = 250;

    protected $schemItems = [
        0 => [0, [], 'id', 'Id колонки', true, true, 'text', '', '', [], [], []],
        1 => [1, [], 'ausData', 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
        2 => [2, [], 'newName', 'Новое имя', true, true, 'text', '', '', [], [], []],
        3 => [3, [], 'origName', 'Оригинальное имя', true, false, 'text', '', '', [], [], []],
        4 => [4, [], 'url', 'Ссылка', true, false, 'text', '', '', [], [], []],
        5 => [5, [], 'type', 'Тип', true, false, 'text', '', '', [], [], []],
        6 => [6, [], 'size', 'Размер', true, false, 'numb', '', '', [], [], []],
        7 => [7, [], 'width', 'Ширина', true, false, 'numb', '', '', [], [], []],
        8 => [8, [], 'height', 'Высота', true, false, 'numb', '', '', [], [], []],
        9 => [9, [], 'duration', 'Длительность', true, false, 'numb', '', '', [], [], []],
        10 => [10, [], 'props', 'Другие свойства', false, false, 'list', '', '', [], [], []],        
    ];
    

    public $types = [
        1 => ["Изображение"],
        2 => ["Видео"],
        3 => ["Аудио"],
        4 => ["Архив"],
        5 => ["Документ"],
    ];

    protected $mime_types = [
        // Видео
        "video/mp4" => ["MP4 видео файлы", "Видео"],
        "video/quicktime" => ["QuickTime видео файлы", "Видео"],
        "video/mpeg" => ["MPEG видео файлы", "Видео"],
        "video/x-msvideo" => ["AVI видео файлы", "Видео"],
        "video/x-matroska" => ["Matroska (MKV) видео файлы", "Видео"],
        "video/webm" => ["WebM видео файлы", "Видео"],
        "video/3gpp" => ["3GPP видео файлы", "Видео"],
        "video/3gpp2" => ["3GPP2 видео файлы", "Видео"],
        "video/mp2t" => ["MPEG-2 транспортный поток файлы", "Видео"],
        "video/ogg" => ["OGG видео файлы", "Видео"],
        "video/x-ms-wmv" => ["Windows Media Video файлы (WMV)", "Видео"],
        "video/x-flv" => ["Flash видео файлы (FLV)", "Видео"],
        "video/x-m4v" => ["M4V видео файлы", "Видео"],
        "video/MP2T" => ["MPEG-2 транспортные потоки (M2TS)", "Видео"],
        "video/x-ms-asf" => ["Advanced Systems Format файлы (ASF)", "Видео"],
        "video/divx" => ["DivX видео файлы", "Видео"],
        "application/vnd.apple.mpegurl" => ["для m3u8 плейлистов", "Видео"],
        "application/x-mpegURL" => ["альтернатива для m3u8 плейлистов", "Видео"],

        // Аудио
        "audio/mpeg" => ["MP3 аудио файлы", "Аудио"],
        "audio/aac" => ["AAC аудио файлы", "Аудио"],
        "audio/ogg" => ["OGG аудио файлы", "Аудио"],
        "audio/wav" => ["WAV аудио файлы", "Аудио"],
        "audio/midi" => ["MIDI аудио файлы (.mid, .midi)", "Аудио"],
        "audio/x-midi" => ["альтернативный MIME-тип для MIDI файлов", "Аудио"],
        "audio/webm" => ["WebM аудио файлы", "Аудио"],
        "audio/flac" => ["FLAC аудио файлы", "Аудио"],
        "audio/3gpp" => ["3GPP аудио файлы", "Аудио"],
        "audio/3gpp2" => ["3GPP2 аудио файлы", "Аудио"],
        "audio/aiff" => ["AIFF аудио файлы", "Аудио"],
        "audio/x-aiff" => ["альтернативный MIME-тип для AIFF файлов", "Аудио"],
        "audio/x-ms-wma" => ["Windows Media Audio файлы (WMA)", "Аудио"],
        "audio/amr" => ["AMR аудио файлы", "Аудио"],
        "audio/x-caf" => ["Core Audio Format файлы (CAF)", "Аудио"],

        // Документы
        "text/rtf" => ["Rich Text Format файлы (.rtf)", "Документы"],
        "application/pdf" => ["PDF файлы (.pdf)", "Документы"],
        "application/msword" => ["Microsoft Word файлы (.doc)", "Документы"],
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => ["Microsoft Word файлы (.docx)", "Документы"],
        "application/vnd.ms-excel" => ["Microsoft Excel файлы (.xls)", "Документы"],
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => ["Microsoft Excel файлы (.xlsx)", "Документы"],
        "application/vnd.ms-powerpoint" => ["Microsoft PowerPoint файлы (.ppt)", "Документы"],
        "application/vnd.openxmlformats-officedocument.presentationml.presentation" => ["Microsoft PowerPoint файлы (.pptx)", "Документы"],
        "application/vnd.oasis.opendocument.text" => ["OpenDocument текстовые файлы (.odt)", "Документы"],
        "application/vnd.oasis.opendocument.spreadsheet" => ["OpenDocument таблицы (.ods)", "Документы"],
        "application/vnd.oasis.opendocument.presentation" => ["OpenDocument презентации (.odp)", "Документы"],
        "application/vnd.oasis.opendocument.graphics" => ["OpenDocument графические файлы (.odg)", "Документы"],
        "application/vnd.google-apps.document" => ["Google Документы (файлы, экспортированные из Google Docs)", "Документы"],
        "application/vnd.google-apps.spreadsheet" => ["Google Таблицы (файлы, экспортированные из Google Sheets)", "Документы"],
        "application/vnd.google-apps.presentation" => ["Google Презентации (файлы, экспортированные из Google Slides)", "Документы"],
        "application/x-iwork-keynote-sffkey" => ["Apple Keynote файлы (.key)", "Документы"],
        "application/x-iwork-numbers-sffnumbers" => ["Apple Numbers файлы (.numbers)", "Документы"],
        "application/vnd.apple.pages" => ["Apple Pages файлы (.pages)", "Документы"],
        "application/epub+zip" => ["EPUB файлы (.epub)", "Документы"],
        "application/x-mobipocket-ebook" => ["Mobipocket файлы (.mobi)", "Документы"],
        "text/plain" => ["текстовые файлы (.txt)", "Документы"],
        "text/csv" => ["файлы CSV (Comma-Separated Values)", "Документы"],
        "text/tab-separated-values" => ["файлы TSV (Tab-Separated Values)", "Документы"],
        "text/html" => ["HTML файлы (.html, .htm)", "Документы"],
        "text/css" => ["CSS файлы (.css)", "Документы"],
        "application/javascript" => ["JavaScript файлы (.js)", "Документы"],
        "application/json" => ["JSON файлы (.json)", "Документы"],
        "application/xml" => ["XML файлы (.xml)", "Документы"],
        "text/xml" => ["альтернативный MIME-тип для XML файлов", "Документы"],
        "application/postscript" => ["PostScript файлы (.ps, .eps)", "Документы"],
        "application/vnd.ms-outlook" => ["Microsoft Outlook файлы (.msg)", "Документы"],
        "application/x-latex" => ["LaTeX файлы (.latex)", "Документы"],
        "application/x-tex" => ["TeX файлы (.tex)", "Документы"],

        // Архивы
        "application/zip" => ["ZIP архивы (.zip)", "Архивы"],
        "application/x-zip-compressed" => ["ZIP архивы (.zip) альтернативный", "Архивы"],
        "application/x-rar-compressed" => ["RAR архивы (.rar)", "Архивы"],
        "application/x-7z-compressed" => ["7z архивы (.7z)", "Архивы"],
        "application/gzip" => ["GZIP архивы (.gz)", "Архивы"],
        "application/x-tar" => ["TAR архивы (.tar)", "Архивы"],

        // Изображения
        "image/jpeg" => ["JPEG изображения", "Изображения"],
        "image/png" => ["PNG изображения", "Изображения"],
        "image/gif" => ["GIF изображения", "Изображения"],
        "image/webp" => ["WebP изображения", "Изображения"],
        "image/bmp" => ["BMP изображения", "Изображения"],
        "image/tiff" => ["TIFF изображения", "Изображения"],
        "image/svg+xml" => ["SVG изображения", "Изображения"],
        "image/x-icon" => ["ICO изображения", "Изображения"],
        "image/vnd.microsoft.icon" => ["ICO изображения (альтернативный MIME-тип)", "Изображения"],
    ];

    protected $allowedVideo = [
        // Видео,
        "video/mp4",
        "video/quicktime",
        "video/mpeg",
        "video/x-msvideo",
        "video/x-matroska",
        "video/webm",
    ];
    protected $allowedAudio = [
        // Аудио,
        "audio/mpeg",
        "audio/aac",
        "audio/ogg",
        "audio/wav",
        "audio/webm",
        "audio/x-aiff",
        "audio/x-ms-wma",
    ];
    protected $allowedDocs = [
        // Документы,
        "text/rtf",
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-powerpoint",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "application/vnd.oasis.opendocument.text",
        "application/vnd.oasis.opendocument.spreadsheet",
        "application/vnd.oasis.opendocument.presentation",
        "application/vnd.oasis.opendocument.graphics",
        "application/vnd.google-apps.document",
        "application/vnd.google-apps.spreadsheet",
        "application/vnd.google-apps.presentation",
        "application/x-iwork-keynote-sffkey",
        "application/x-iwork-numbers-sffnumbers",
        "application/vnd.apple.pages",
        "application/epub+zip",
        "application/x-mobipocket-ebook",
        "text/plain",
    ];
    protected $allowedArch = [
        // Архивы,
        "application/zip",
        "application/x-zip-compressed",
        "application/x-rar-compressed",
        "application/x-7z-compressed",
    ];
    protected $allowedImages = [
        // Изображения
        "image/jpeg",
        "image/png",
        "image/gif",
        "image/webp",
        "image/bmp",
        "image/heic",
    ];

    protected $errorMess = [
        "UPLOAD_ERR_INI_SIZE" => "Размер файла превышает допустимый предел (upload_max_filesize)",
        "UPLOAD_ERR_FORM_SIZE" => "Размер файла превышает значение MAX_FILE_SIZE, указанное в HTML-форме",
        "UPLOAD_ERR_PARTIAL" => "Файл был загружен только частично",
        "UPLOAD_ERR_NO_FILE" => "Файл не был загружен",
        "UPLOAD_ERR_NO_TMP_DIR" => "Отсутствует временная папка",
        "UPLOAD_ERR_CANT_WRITE" => "Не удалось записать файл на диск",
        "UPLOAD_ERR_EXTENSION" => "PHP-расширение остановило загрузку файла",
        'default' => "Неизвестная ошибка",
    ];

    public function __construct(string $dbName='', string $dbPath='', string $indexType='') {
        // Вызов родительского конструктора
        parent::__construct($dbName, $dbPath, $indexType);

        // Извлечение директории из $dbPath
        $dbDir = dirname($dbPath);

        // Назначение дополнительных переменных
        $this->uploadDir = $dbDir . '/uploads/';
        $this->imgMediumDir = $this->uploadDir . 'm/';
        $this->imgSmallDir = $this->uploadDir . 's/';

        // Проверка и создание директории, если она не существует
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        if (!is_dir($this->imgMediumDir)) {
            mkdir($this->imgMediumDir, 0755, true);
        }
        if (!is_dir($this->imgSmallDir)) {
            mkdir($this->imgSmallDir, 0755, true);
        }
    }


    public function del ($id)
    {        
        $lastInfo = $this->data->get($id);
        
        
        
        if ($lastInfo){
            $fileName = basename($lastInfo[2]);
            $filePath =  $this->uploadDir.$fileName;
            //echo $filePath;

            if (file_exists($filePath)) unlink($filePath);
        
            if ($lastInfo[5] == 'image'){
                
                $imgMedium = $this->imgMediumDir.$fileName;
                $imgSmall  = $this->imgSmallDir.$fileName;

                if (file_exists($imgMedium))  unlink($imgMedium);
                if (file_exists($imgSmall))  unlink($imgSmall);
            }

        }

        
        if ($this->data->del($id)){
            //$this->updateLinkedBasesNew($id, [], $lastInfo);
            return true;
        }
        

        return false;
    }
    

    public function upload () : string {
        $response = [
            'success' => false,
            'error' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response ['error'] = 'Неверный метод запроса';
            throw new TextDataModelException($response['error']);
        }

        if (!isset($_FILES['file'])) {
            $response ['error'] = 'Файл не был загружен';
            throw new TextDataModelException($response['error']);
        }

        $file = $_FILES['file'];
        $tmpFilePath = $file['tmp_name'];

        // Проверка ошибок загрузки
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $response['error'] = $this->errorMess[$file['error']] ?? $this->errorMess["default"];
            throw new TextDataModelException($response['error']);
        }

        // Проверка размера файла
        /*
        $maxFileSize = 10 * 1024 * 1024; // 10 MB
        if ($file['size'] > $maxFileSize) {
            $response['error'] = 'Файл слишком большой. Пожалуйста, выберите файл меньшего размера.';
            throw new TextDataModelException($response['error']);
        }
        */

        //Получаем оригинальное имя файла
        $originalFileName = basename($file['name']);
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

        // Проверка типа файла и других условий по необходимости
        //$allowedTypes = array_merge($this->allowedVideo, $this->allowedAudio, $this->allowedDocs, $this->allowedArch, $this->allowedImages);

        //Добавляем определение heic фото
        //if (strtolower($fileExtension) == 'heic')  $file['type'] = 'image/heic';

        $type = null;
        if (in_array($file['type'], $this->allowedImages)) $type = 'image';
        if (in_array($file['type'], $this->allowedAudio)) $type = 'audio';
        if (in_array($file['type'], $this->allowedVideo)) $type = 'video';
        if (in_array($file['type'], $this->allowedDocs)) $type = 'doc';
        if (in_array($file['type'], $this->allowedArch)) $type = 'arch';

        $response['type'] = $type;

        if (!$type) {
            $response['error'] = 'Недопустимый тип файла '.$file['type'];
            throw new TextDataModelException($response['error']);
        }

        // Создание уникального имени файла
        $uniqueName = uniqid();
        $uniqueFileName = $uniqueName . '.' . $fileExtension;
        $filePath = $this->uploadDir . $uniqueFileName;

        //получение параметров изображения и подготовка уменьшеных картинок
        if ($type == 'image') {
            list($width, $height) = getimagesize($tmpFilePath);
            //$response['width'] = $width;
            //$response['height'] = $height;
        }

        //получение длительности медиа файлов
        if ($type == 'audio' || $type == 'video') {
            $duration = $this->getMediaDuration($tmpFilePath);
            //$response['duration'] = $duration;
        }

        if ($type != 'image') {
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                $response['error'] = 'Ошибка при перемещении файла';
                throw new TextDataModelException($response['error']);
            }

        } else {

            // Путь для сохранения уменьшенных изображений
            //$mediumFilePath = $this->imgMediumDir . $uniqueFileName;
            $uniqueFileName = $uniqueName . '.jpg';
            $filePath = $this->uploadDir . $uniqueFileName;
            $mediumFilePath = $this->imgMediumDir . $uniqueFileName;
            $smallFilePath = $this->imgSmallDir . $uniqueFileName;

            

            // Конвертирование изображений и создание форматов medium и small
            $response['convertImage']  = $this->imgResizeToJpg ($file['tmp_name'], $filePath, $this->imgOrigSize, $this->imgOrigSize, 80);
            $response['convertMedium'] = $this->imgResizeToJpg ($file['tmp_name'], $mediumFilePath, $this->imgMediumSize, $this->imgMediumSize, 80);
            $response['convertSmall']  = $this->imgResizeToJpg ($file['tmp_name'], $smallFilePath, $this->imgSmallSize, $this->imgSmallSize, 80);

            if ($response['convertImage'] > 0){
                if ($response['convertImage'] == 1) $err_text = 'исходный файл не найден';
                if ($response['convertImage'] == 2) $err_text = 'не удалось получить параметры файла';
                if ($response['convertImage'] == 3) $err_text = 'не существует подходящей функции преобразования';
                if ($response['convertImage'] == 4) $err_text = 'не существует подходящей функции сохранения';
                $response['error'] = $err_text;
                throw new TextDataModelException($response['error']);
            }
        }

        $response['success'] = true;
        $response['originalFileName'] = $originalFileName;
        $response['fileName'] = $uniqueFileName;

        // Получение имени базы и id элемента
        $response['fieldId'] = $_POST['fieldId'] ?? '';
        $baseName = $_POST['baseName'] ?? '';
        $itemId = $_POST['itemId'] ?? '';
        //$response['baseName'] = $baseName;
        //$response['itemId'] = $itemId;

        //Сохранение информации о файле в базу
        $tobase = [
            2 => $uniqueFileName,
            3 => $originalFileName,
            4 => $filePath, // url
            5 => $type, //type
            6 => $file['size'] ?? 0,
            7 => $width ?? '',
            8 => $height ?? '',
            9 => $duration ?? '',
            10 => [],
        ];
        

        $newId = $this->data->add($tobase);

        //$response['id'] = $newId;

        return $newId;
    }

    protected function getMediaDuration($filePath)
    {
        $getID3 = new getID3();
        $fileInfo = $getID3->analyze($filePath);

        if (isset($fileInfo['playtime_seconds'])) {
            return round($fileInfo['playtime_seconds'], 2);
        }

        return -1;
    }

    // Функция для изменения размера изображения с сохранением пропорций и конвертацией в JPEG
    protected function imgResizeToJpg($src, $dest, $maxWidth, $maxHeight, $quality=90) {

        if (!file_exists($src)) return 1; // исходный файл не найден
        $size = getimagesize($src);
        if ($size === false) return 2; // не удалось получить параметры файла

        // Определяем исходный формат по MIME-информации и выбираем соответствующую imagecreatefrom-функцию.
        $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
        $imgCreateFunc = "imagecreatefrom" . $format;
        if (!function_exists($imgCreateFunc)) return 3; // не существует подходящей функции преобразования
        $imgSaveFunc = "image" . $format;
        if (!function_exists($imgSaveFunc)) return 4; // не существует подходящей функции сохранения

        // Определяем необходимость преобразования размера
        $ratio = 1;
        if ($maxWidth < $size[0] || $maxHeight < $size[1]) {
            $ratio = min($maxWidth / $size[0], $maxHeight / $size[1]);
        }

        $newWidth = floor($size[0] * $ratio);
        $newHeight = floor($size[1] * $ratio);
        $isrc = $imgCreateFunc($src);
        $idest = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $newWidth, $newHeight, $size[0], $size[1]);
        //imagejpeg($idest, $dest, $quality);
        $imgSaveFunc($idest, $dest);
        chmod($dest, 0666);
        imagedestroy($isrc);
        imagedestroy($idest);
        return 0; // успешно
    }
}