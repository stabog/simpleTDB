<?

require_once "vendor/autoload.php";

require_once "php/Core/SimpleTdb/TDBInterface.php";
require_once "php/Core/SimpleTdb/TextDataBase.php";
require_once "php/Core/SimpleTdb/TextDataBaseModel.php";
require_once "php/Core/SimpleTdb/TextDataImport.php";
require_once "php/Core/SimpleTdb/TextDataSchem.php";
require_once "php/Core/SimpleTdb/TextDataForm.php";
require_once "php/Core/SimpleTdb/ArrayHelpers.php";
require_once "php/Core/SimpleTdb/FormHelpers.php";

require_once "php/Modules/Uploads.php";

session_start();


$upload = new Uploader();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = $upload->uploadAPI ();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    echo $upload->showForm ();
}



class Uploader{
    protected $uploadDir = 'uploads/';
    protected $imgMediumDir;
    protected $imgSmallDir;

    protected $model;

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

    protected $html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Загрузка файлов</title>
    <link rel="stylesheet" type="text/css" href="/vendor/fomantic/ui/dist/semantic.min.css">
    <style>
        #main {max-width: 1000px; margin: 10px auto;}
    </style>
</head>
<body>

<div id="main">
    <h2>Загрузка файлов</h2>
    <form class="ui form" action="#" method="post" id="schemForm">
        <div class="field ">
            <label>Файлы</label>            
            <div class="inline fields">
                <div class="field">                    
                    <div class="ui left icon input">
                        <i class="file upload icon"></i>
                        <input class="fileInput" type="file" name="form_info[files][]" placeholder="" value="" multiple
                            data-fieldid="1" data-basename="testBase" data-itemid="testId">
                    </div>
                </div>            
            </div>
            <div class="ui segments fileList"></div>
        </div>
    </form>
</div>

<script src="/js/async_file_load.js" async></script>
<script src="/js/heic2any.min.js" async></script>

</body>
</html>';

    public function __construct ()
    {
        $this->model = new FileSearcher\Uploads();
        $this->imgMediumDir = $this->uploadDir.'m/';
        $this->imgSmallDir = $this->uploadDir.'s/';

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        if (!is_dir($this->imgMediumDir)) {
            mkdir($this->imgMediumDir, 0777, true);
        }
        if (!is_dir($this->imgSmallDir)) {
            mkdir($this->imgSmallDir, 0777, true);
        }
    }

    public function showForm()
    {
        return $this->html;
    }

    public function uploadAPI () : array {
        $response = [
            'success' => false,
            'error' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response ['error'] = 'Неверный метод запроса';
            return $response;
        }        
        
        if (!isset($_FILES['file'])) {
            $response ['error'] = 'Файл не был загружен';
            return $response;
        }

        $file = $_FILES['file'];
        $tmpFilePath = $file['tmp_name'];

        // Проверка ошибок загрузки
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $response['error'] = $this->errorMess[$file['error']] ?? $this->errorMess["default"];                    
            return $response;
        }

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
            return $response;
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
                return $response;                
            }

        } else {
           

            // Путь для сохранения уменьшенных изображений
            //$mediumFilePath = $this->imgMediumDir . $uniqueFileName;
            $uniqueFileName = $uniqueName . '.jpg';            
            $filePath = $this->uploadDir . $uniqueFileName;
            $mediumFilePath = $this->imgMediumDir . $uniqueFileName;
            $smallFilePath = $this->imgSmallDir . $uniqueFileName;

            // Конвертирование изображений и создание форматов medium и small
            $response['convertImage'] = $this->imgResizeToJpg($file['tmp_name'], $filePath, 2500, 2500, 80);
            $response['convertMedium'] = $this->imgResizeToJpg($filePath, $mediumFilePath, 1000, 1000, 80);
            $response['convertSmall'] = $this->imgResizeToJpg($filePath, $smallFilePath, 200, 200, 90);
            
            if ($response['convertImage'] > 0){
                if ($response['convertImage'] == 1) $err_text = 'исходный файл не найден';
                if ($response['convertImage'] == 2) $err_text = 'не удалось получить параметры файла';
                if ($response['convertImage'] == 3) $err_text = 'не существует подходящей функции преобразования';
                if ($response['convertImage'] == 4) $err_text = 'не существует подходящей функции сохранения';
                $response['error'] = $err_text;
                return $response;
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
            2 => $filePath,
            3 => $originalFileName,
            4 => '', // url
            5 => $type, //type
            6 => $file['size'] ?? 0,
            7 => $width ?? '',
            8 => $height ?? '',
            9 => $duration ?? '',
            10 => [],
        ];

        $newId = $this->model->add($tobase);

        $response['id'] = $newId;        
        
        return $response;
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
?>



