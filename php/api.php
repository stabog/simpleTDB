<?php
require_once "vendor/autoload.php";

require_once "php/Core/SimpleTdb/TDBInterface.php";
require_once "php/Core/SimpleTdb/TextDataBase.php";

require_once "php/Core/SimpleTdb/TextDataModel.php";
require_once "php/Core/SimpleTdb/TextDataModelException.php";
require_once "php/Core/SimpleTdb/TextDataSchem.php";

require_once "php/Core/SimpleTdb/TextDataAuth.php";
require_once "php/Core/SimpleTdb/TextDataModelUploads.php";
require_once "php/Core/SimpleTdb/TextDataModelUsers.php";
require_once "php/Core/SimpleTdb/TextDataModelSessions.php";

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


use SimpleTdb\TextDataBase as TDB;
use SimpleTdb\TextDataModel as TDM;
use SimpleTdb\TextDataModelException;
use SimpleTdb\TextDataAuth;

use SimpleTdb\TextDataModelUploads as TDUploads;
use SimpleTdb\TextDataModelUsers as TDUsers;

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


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

header('Content-Type: application/json');

if (!isset($links[1])) {
    echo json_encode(['error' => 'ID приложения не указано'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($links[2])) {
    echo json_encode(['error' => 'ID базы данных не указано'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($act)) {
    echo json_encode(['error' => 'Action не указан'], JSON_UNESCAPED_UNICODE);
    exit;
}

$appName = $links[1];
$baseName = $links[2];
$dbPath = 'apps/'.$appName.'/db';

switch ($baseName) {
    case 'uploads':
        $model = new TDUploads($baseName, $dbPath, 'guid');
        break;
    case 'users':
        $model = new TDUsers($baseName, $dbPath, 'guid');
        break;
    case 'auth':
        $model = new TextDataAuth($dbPath);
        break;
    default:
        $model = new TDM($baseName, $dbPath, 'guid');
        break;
}

$uploads = new TDUploads('uploads', $dbPath, 'guid');
$users = new TDUsers('users', $dbPath, 'guid');
$auth = new TextDataAuth($dbPath);


if ($baseName != 'auth'){
    $model->setRespFormatToDict([
        "idToKeys" => false,
    ]);
}


$id = $id ?? null;
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$data['item'] = (isset($data['item']) && is_array($data['item']))? $data['item'] : null;


$nonCriticalErrors = [];
$result = false;


try {
    switch ($act) {
        case 'get':            
            $result["item"] = $model->get($id);
            $result["sucess"] = true;
            break;

        case 'all':            
            $result["items"] = $model->all();
            $result["sucess"] = true;
            break;

        case 'add':
            $result["item_id"] = $model->add($data['item']);
            $result["sucess"] = true;
            break;

        case 'upd':
            $result["sucess"] = $model->upd($id, $data['item']);
            break;

        case 'del':
            $result["sucess"] = $model->del($id);
            break;


        //Схема
        case 'getSchem':            
            $result["schem"] = $model->getSchem();
            $result["sucess"] = true;
            break;
        
        //Файлы
        case 'upload':            
            $result["item_id"] = $uploads->upload();
            $result["sucess"] = true;
            break;

        //Пользователь
        case 'reg':            
            $result["item_id"] = $auth->register($data['item']);
            $result["sucess"] = true;
            break;
        
        case 'signIn':            
            $result["session_id"] = $auth->signin($data['item']);
            $result["sucess"] = true;
            break;

        case 'getSess':            
            $result["item_id"] = $auth->getSession($id);
            $result["sucess"] = true;
            break;

        case 'signOut':            
            $result["sucess"] = $auth->signout($id);
            break;

        case 'getUserIdByEmail':            
            $result["item_id"] = $users->getUserIdByEmail($id);
            $result["sucess"] = true;
            break;

        default:
            $result = ['error' => 'Invalid action'];
            break;
    }
} catch (TextDataModelException $e) {
    $result['error'] = $e->getMessage();
}

if (isset($result['error']) or !$result["sucess"]) {
    $error_text = $result['error'] ?? 'Произошла ошибка при выполнения действия';
    echo json_encode(['error' => $error_text], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} 