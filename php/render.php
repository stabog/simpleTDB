<?

if (isset($_GET)) {foreach ($_GET as $key => $val) {$$key = $val;}}
if (isset($_POST)) {foreach ($_POST as $key => $val) {$$key = $val;}}
if (isset($_COOKIE)) {foreach ($_COOKIE as $key => $val) {$$key = $val;}}

$links = isset($links) ? $links : [];

$content = [
    "title" => "Пример заголовка",
    "description" => "Пример описания"
];

$html = '';

if (!isset($links[0])){
    include 'views/index.html';
} else if ($links[0] == "upload"){
    include 'views/upload.html';
} else {
    
}
