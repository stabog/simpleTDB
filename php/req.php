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
require_once "php/Modules/Requirements.php";

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

use Requirements as Requirements;

$requirements = new Requirements();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    
    $inputFile = $_FILES['file']['tmp_name'];
    $requirements->convertExcelToJson($inputFile);
} else {
    $requirements->showUploadForm();
}


