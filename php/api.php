<?php
require_once "vendor/autoload.php";

require_once "php/Core/SimpleTdb/TDBInterface.php";
require_once "php/Core/SimpleTdb/TextDataBase.php";

require_once "php/Core/SimpleTdb/TextDataModel.php";
require_once "php/Core/SimpleTdb/TextDataModelException.php";
require_once "php/Core/SimpleTdb/TextDataSchem.php";
require_once "php/Core/SimpleTdb/FormHelpers.php";


use SimpleTdb\TextDataBase as TDB;
use SimpleTdb\TextDataModel as TDM;
use SimpleTdb\TextDataModelException;

const FDB_PATH = 'db';
const SITE_NAME = 'simpleTDB';


if (isset($_GET)) {foreach ($_GET as $key => $val) {$$key = $val;}}
if (isset($_POST)) {foreach ($_POST as $key => $val) {$$key = $val;}}
if (isset($_COOKIE)) {foreach ($_COOKIE as $key => $val) {$$key = $val;}}

//session_start();



$root = TDB::getInstance('root', FDB_PATH);
//$model->add([3, "", "test", "test 1"]);
//$model->upd(4, [3, "", "test", "test 123"]);

//print_r($links);



header('Content-Type: application/json');

if (!isset($links[1])) {
    echo json_encode(['error' => 'ID приложения не указано']);
    exit;
}

if (!isset($links[2])) {
    echo json_encode(['error' => 'ID базы данных не указано']);
    exit;
}

if (!isset($act)) {
    echo json_encode(['error' => 'Action не указан']);
    exit;
}

$appName = $links[1];
$baseName = $links[2];
$dbPath = 'apps/'.$appName.'/db';
$model = new TDM($baseName, $dbPath, 'guid');
$model->setRespFormatToDict(
    $separateKeys = true,
);

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

        case 'getSchem':            
            $result["schem"] = $model->getSchem();
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
    echo json_encode(['error' => $error_text]);
} else {
    echo json_encode($result);
} 