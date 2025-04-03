<?
namespace FileSearcher;

use EvalTools\ProjectManage;
use EvalTools\UsersSess;

require_once "vendor/autoload.php";

require_once "php/Core/SimpleTdb/TDBInterface.php";
require_once "php/Core/SimpleTdb/TextDataBase.php";
require_once "php/Core/SimpleTdb/TextDataBaseModel.php";
require_once "php/Core/SimpleTdb/TextDataImport.php";
require_once "php/Core/SimpleTdb/TextDataSchem.php";
require_once "php/Core/SimpleTdb/TextDataSchemForm.php";
require_once "php/Core/SimpleTdb/TextDataForm.php";
require_once "php/Core/SimpleTdb/ArrayHelpers.php";
require_once "php/Core/SimpleTdb/FilterHelpers.php";
require_once "php/Core/SimpleTdb/FormHelpers.php";  //Дубликаты


require_once "php/Core/StringHelper.php";
require_once "php/Core/UploadFilesHelper.php";
require_once "php/Core/ImagesHelper.php";
require_once "php/Core/ViewHelper.php";
require_once "php/Core/YoutubeHelper.php";
require_once "php/Core/FilterHelper.php";
require_once "php/Core/TableHelper.php";
require_once "php/Core/FormHelper.php"; //Дубликаты

require_once "php/Core/Users/Sessions.php";
require_once "php/Core/Users/Users.php";
require_once "php/Core/Users/User.php";
require_once "php/Core/Users/UsersAuth.php";
require_once "php/Core/Users/UsersSess.php";


//require_once "php/Modules/File.php";
//require_once "php/Modules/Files.php";
//require_once "php/Modules/FilesController.php";

require_once "php/Core/SimpleTdb/ModelsFactory.php";

require_once "php/Modules/BasesController.php";
require_once "php/Modules/BaseController.php";

require_once "php/Modules/HomeWorksController.php";


require_once "php/Modules/Users.php";
require_once "php/Modules/Uploads.php";



const FDB_PATH = 'db/';
const SITE_NAME = 'fsearch';


if (isset($_GET)) {foreach ($_GET as $key => $val) {$$key = $val;}}
if (isset($_POST)) {foreach ($_POST as $key => $val) {$$key = $val;}}
if (isset($_COOKIE)) {foreach ($_COOKIE as $key => $val) {$$key = $val;}}

session_start();



$html = $html_js ='';
$top_text = '';
$useMainTpl = true;

$acepted_links = [ 'auth', 'bases', 'homeworks', 'factory'];

//Сессия
$sess = new \SimpleUsers\Sessions ();
$sess_act = $sess_act ?? null;
$sess_id = $sess_id ?? null;
$sess->run($sess_act, $sess_id);


//Определяем пользователя
$usersMan = new \SimpleUsers\Users();
$curUserId = $curUser = null;
if (isset($_SESSION["curUserId"])){
    $curUserId = $_SESSION["curUserId"];
    $curUser = $usersMan->getUser($curUserId);
}

//add test user
//$info[2] = ['test@vreader.ru', 'vreader'];
//$info[3] = ['Пользователь', 'Тестовый'];
//$users->addUser($info);

$render = true;

//Страница по умолчанию
if (!isset($links) or count($links) == 0) {
    $html .= '<div class="ui warning message">Главная страница</div>';
    $render = false;
    $useMainTpl = false;
}

//Страница не найдена
if ($render and !in_array($links[0], $acepted_links)){
    $html .= '<div class="ui warning message">Неправильная ссылка</div>';
    $render = false;
}

//Авторизация
if ($render and $links[0] == 'auth'){
    $act = $act ?? 'form';
    $auth = new \SimpleUsers\UsersAuth($usersMan, $sess);
    $html .= $auth->run($act);
    $render = false;
}

//Обработка


$act = $act ?? '';
$id = $id ?? '';
$props = $props ?? [];

// Использование фабрики для создания объектов и настройки зависимостей
$models = \SimpleTdb\ModelsFactory::createModels();

if ($render and $links[0] == 'factory'){    
    
    foreach ($models as $baseName => $model){
        $html .= $baseName.'<br>
        '.$model->showDependency().'
        <br>';
    }

}


if ($render and $links[0] == 'bases'){

    //$bases = new \SimpleTdb\TextDataBaseModel('root', '', 'guid');
    //$bc = new BasesController ($bases, $links);
    //$models = $bc->getModels($links);
    //$cbc = new BaseController ($models, $bc->cureModelId, $bc->cureModelName);
    
    $cureModelName = current(array_keys($models));
    if (isset($links[1]) and isset($models[$links[1]])){
        $cureModelName = $links[1];
    }

    $cbc = new BaseController ($models, $cureModelName);    
    
    $tpl_type = 'data';
    
    $html .= $cbc->viewRootMenu ();
    $html .= $cbc->run ($act, $id, $props);

    /*
    if (!isset($links[1])){
        $content["title"] = 'Управление базами';
        $cbc->run ($act, $id, $props);
    } else {
        $content["title"] = 'Управление базой '.$bc->cureModelName;
        $html .= $cbc->run ($act, $id, $props);
    }
    */
    

    $render = false;
    
}

if ($render and $links[0] == 'homeworks'){
    

    $bases = new \SimpleTdb\TextDataBaseModel('root', '', 'guid');
    $bc = new BasesController ($bases, $links);
    $models = $bc->getModels($links);

    $modelId = '081ddc1c-6a9b-47e7-bce8-23299e3c8933';
    $modelName = 'homeworks';
    $hwc = new HomeWorksController ($models, $modelId, $modelName);    
    
    $tpl_type = 'data';
    //$html .= $bc->viewMenu ();

    
    if (!isset($links[1])){        
        $content["title"] = 'Домашние задания';
    } else {
        $act = $links[1];
        $content["title"] = 'Просмотр ДЗ';        
    }

    $html .= $hwc->run ($act, $id, $props);
    
    

    $render = false;
    
}






//Вывод сообщений
$mess_text = '';
if (isset($_SESSION["message"])) {
    $mess_text = showMessage($_SESSION["message"]);
    unset($_SESSION["message"]);
}


$head_class = $main_class = '';
if (isset($tpl_type) and $tpl_type == 'data'){
    $head_class = $main_class =  ' wide';
}

$html = '
<div id="head" class="'.$head_class.'">
    <div class="head-wrapper">
        <div class="head-logo">
            <a href="/">
                <img src="/img/zest_logo.png" />
            </a>
        </div>    
        <div class="ui compact menu head-menu-right ">        
            '.$usersMan->viewUserMenu($curUserId).'
        </div>
    </div>
</div>
<div id="main" class="'.$main_class.'">
    '.$mess_text.'
    '.$html.'
</div>


';




function showMessage($items)
{

    $html_mess = '';
    foreach ($items as $vals) {
        $mess_class = '';
        if ($vals[0] == 'err') $mess_class = 'negative';
        else if ($vals[0] == 'suc') $mess_class = 'positive';
        else if ($vals[0] == 'war') $mess_class = 'warning';
        

        $html_mess .= '
    <div class="ui ' . $mess_class . ' message">
      <i class="close icon"></i>
      ' . $vals[1] . '
    </div>';
    }


    return $html_mess;
}
