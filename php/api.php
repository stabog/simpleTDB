<?php
require_once "vendor/autoload.php";

require_once "php/Core/SimpleTdb/TDBInterface.php";
require_once "php/Core/SimpleTdb/TextDataBase.php";

require_once "php/Core/SimpleTdb/TextDataModel.php";
require_once "php/Core/SimpleTdb/TextDataModelException.php";
require_once "php/Core/SimpleTdb/TextDataSchem.php";

require_once "php/Core/SimpleTdb/TextDataAuth.php";
require_once "php/Core/SimpleTdb/TextDataModelRoot.php";
require_once "php/Core/SimpleTdb/TextDataModelUploads.php";
require_once "php/Core/SimpleTdb/TextDataModelUsers.php";
require_once "php/Core/SimpleTdb/TextDataModelSessions.php";

require_once "php/Modules/Requests.php";

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


use SimpleTdb\TextDataBase as TDB;
use SimpleTdb\TextDataModel as TDM;
use SimpleTdb\TextDataModelException;
use SimpleTdb\TextDataAuth;

use SimpleTdb\TextDataModelRoot as TDRoot;
use SimpleTdb\TextDataModelUploads as TDUploads;
use SimpleTdb\TextDataModelUsers as TDUsers;

use Requests as TDRequests;

const FDB_PATH = 'db';
const SITE_NAME = 'simpleTDB';


if (isset($_GET)) {foreach ($_GET as $key => $val) {$$key = $val;}}
if (isset($_POST)) {foreach ($_POST as $key => $val) {$$key = $val;}}
if (isset($_COOKIE)) {foreach ($_COOKIE as $key => $val) {$$key = $val;}}

//session_start();



//$root = TDB::getInstance('root', FDB_PATH);
//$model->add([3, "", "test", "test 1"]);
//$model->upd(4, [3, "", "test", "test 123"]);

//print_r($links);

/*
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
*/

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Ответ на preflight-запрос
    header('HTTP/1.1 204 No Content');
    exit;
}

header('Content-Type: application/json');



if (!isset($links[1])) {
    echo json_encode(['error' => 'ID приложения не указано'], JSON_UNESCAPED_UNICODE);
    exit;
}


if (!isset($links[2])) {
    echo json_encode(['error' => 'ID базы данных не указано'], JSON_UNESCAPED_UNICODE);
    exit;
}


$id = $id ?? null;
$tdbPath = $tdbPath ?? null;


$input = file_get_contents('php://input');
$data = json_decode($input, true);
$data['item'] = (isset($data['item']) && is_array($data['item']))? $data['item'] : null;



$appName = $links[1];
$baseName = $links[2];

$appRoot = 'apps/'.$appName;
$dbRoot = '/db';
$dbPath = $appRoot.$dbRoot;
$uploadDbPath = $dbPath;
$uploadPath = '';

if ($tdbPath and $tdbPath != $dbRoot){   
    if (strpos($tdbPath, '/') === 0) {
        $sepDbPath = $appRoot . $tdbPath;
        $uploadPath = $appRoot . $tdbPath;
    } else {
        $sepDbPath = $dbPath . '/' . $tdbPath;
        $uploadPath = $dbPath . '/' . $tdbPath;        
    }

    if (substr($uploadPath, -1) !== '/') {
        $uploadPath .= '/';
    }
    
    $uploads = new TDUploads('uploads', $dbPath, 'guid', $uploadPath);
    $model = new TDM($baseName, $sepDbPath, 'guid', $updByItem=true);

} else {

    $uploads = new TDUploads('uploads', $uploadDbPath, 'guid', $uploadPath);
    $auth = new TextDataAuth($dbPath);
    switch ($baseName) {
        case 'root':
            $model = new TDRoot($baseName, $dbPath, 'num');
            break;
        case 'uploads':
            $model = new TDUploads($baseName, $uploadDbPath, 'guid', $uploadPath);
            break;
        case 'users':
            $model = new TDUsers($baseName, $dbPath, 'guid');
            break;
        case 'auth':
            $model = new TextDataAuth($dbPath);
            break;
        case 'requests':
            $model = new TDRequests($baseName, $dbPath);
            $requests = $model;
            break;
        default:
            $model = new TDM($baseName, $dbPath, 'guid');
            break;
    }
}

//$users = new TDUsers('users', $dbPath, 'guid');


if ($baseName != 'auth'){
    $model->setRespFormatToDict([
        "idToKeys" => false,
    ]);
}





$nonCriticalErrors = [];
$result["success"] = false;


try {
    switch ($act) {
        case 'get':            
            $result["item"] = $model->get($id);
            $result["success"] = true;
            break;

        case 'all':            
            $result["items"] = $model->all();
            $result["success"] = true;
            break;

        case 'add':
            $result["item_id"] = $model->add($data['item']);
            if ($result["item_id"]) $result["success"] = true;
            break;

        case 'addItems':
            $result["items_id"] = $model->add($data['addItems']);
            if ($result["items_id"]) $result["success"] = true;
            break;

        case 'upd':
            $result["success"] = $model->upd($id, $data['item']);
            break;

        case 'del':
            $result["success"] = $model->del($id);
            break;


        //Схема
        case 'getSchem':            
            $result["schem"] = $model->getSchem();
            $result["success"] = true;
            break;

        case 'updSchem':            
            $result["items"] = $model->saveSchem($data);
            $result["success"] = true;
            break;
        
        //Файлы
        case 'upload':            
            $result["item_id"] = $uploads->upload();
            $result["success"] = true;
            break;

        //Пользователь
        case 'reg':            
            $result["item_id"] = $auth->register($data['item']);
            $result["success"] = true;
            break;
        
        case 'signIn':            
            $result["items"] = $auth->signin($data['item']);
            $result["success"] = true;
            break;

        case 'getSess':            
            $result["item_id"] = $auth->getSession($id);
            $result["success"] = true;
            break;

        case 'signOut':            
            $result["success"] = $auth->signout($id);
            break;
            
        case 'changePass':            
            $result["item_id"] = $auth->changePass($data);
            $result["success"] = true;
            break;

        case 'getCounts':
            if ($baseName == "requests"){
                $result["data"] = $requests->countRequests($id);
                $result["success"] = true;
            }            
            break;

        case 'getUserIdByEmail':            
            $result["item_id"] = $users->getUserIdByEmail($id);
            $result["success"] = true;
            break;

        
            
       

        default:
            $result = ['error' => 'Invalid action'];
            break;
    }
} catch (TextDataModelException $e) {
    $result['error'] = $e->getMessage();
}

if (isset($result['error']) or !$result["success"]) {
    $error_text = $result['error'] ?? 'Произошла ошибка при выполнения действия';
    echo json_encode(['error' => $error_text], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

