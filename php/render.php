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
    echo '';
} else if ($links[0] == "adm_form"){
    include 'views/index.html';
} else if ($links[0] == "adm_upload"){
    include 'views/upload.html';
} else {
    echo '';
}
