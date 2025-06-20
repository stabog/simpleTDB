<?php


use \SimpleTdb\TextDataModel as TDM;
use \SimpleTdb\TextDataModelUsers as TDUsers;
use \SimpleTdb\TextDataModelException;
use PhpOffice\PhpSpreadsheet\IOFactory;


/*
$characteristic = [
    'title' => $row['F'],
    'min' => $row['G'],
    'max' => $row['H'],
    'variants' => $variants,
    'main' => $row['J'],
    'unit' => $row['K'],
]
*/
            


class Requirements extends TDM {
    protected $dbName = 'requirements';
    protected $indexType = "num";
    
    
    protected $schemItems = [
        0 => [0, [], 'id', 'id запроса', true, true, 'text', '', '', [], [], []],
        1 => [1, [], 'ausData', 'Create/Edit/Sync информация', true, false, 'list', '', '', [], [], []],
        2 => [2, [], 'title', 'Название', false, false, 'text', '', '', [], [], []],
        3 => [3, [], 'unit', 'Единица измерения', false, false, 'text', '', '', [], [], []],
        4 => [4, [], 'okpd2', 'Код ОКПД2', false, false, 'text', '', '', [], [], []],
        5 => [5, [], 'kkn', 'Код ККН', false, false, 'text', '', '', [], [], []],
        6 => [6, [], 'ktru', 'Код КТРУ', false, false, 'text', '', '', [], [], []],
        7 => [7, [], 'cat', 'Категория', false, false, 'text', '', '', [], [], []],
        8 => [8, [], 'cat_global', 'Глобальная категория', false, false, 'text', '', '', [], [], []],
        9 => [9, [], 'date', 'Дата', false, false, 'text', '', '', [], [], []],
        10 => [10, [], 'is_russian', 'Является ли российским', false, false, 'bool', '', '', [], [], []],
        11 => [11, [], 'characteristics', 'Характеристики', false, false, 'list', '', '', [], [], []],
    ];

    private $log = [];

    public function __construct(string $dbName='', string $dbPath='', string $indexType='') {
        // Вызов родительского конструктора
        parent::__construct($dbName, $dbPath, $indexType);                
    }

    private function logStep(string $message): void
    {
        $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        $text = sprintf("[%.3f] %s", $time, $message);
        $this->log[] = $text;
        echo "<pre>" . $text . "</pre>";
        ob_flush();
        flush();
    }
    

    public function convertExcelToJson(string $inputFile): void
    {
        $this->logStep("Начало выполнения скрипта");

        $spreadsheet = IOFactory::load($inputFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $sheetName = $worksheet->getTitle();
        $sheetData = $worksheet->toArray(null, true, true, true);

        if (empty($sheetData)) {
            exit;
        }

        $this->logStep("Данные из Excel загружены");

        // Очистка базы данных перед добавлением новых элементов
        $this->data->clean();
        $this->logStep("База данных очищена");

        $rows = [];
        $currentItem = [];
        $count = 0;
        $globalCategories = [];

        foreach ($sheetData as $rowId => $row) {
            if ($rowId < 5) continue;

            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            if (!empty($row['A'])) {
                if (!empty($currentItem)) {
                    $rows[] = $currentItem;
                }
                $currentItem = [
                    'title' => $row['B'],
                    'unit' => $row['E'],
                    'okpd2' => $row['C'],
                    'kkn' => $row['N'],
                    'ktru' => $row['M'],
                    'cat' => $row['L'],
                    'cat_global' => $this->getGlobalCategoryKey($row['O'], $globalCategories),
                    'date' => $row['P'] ? date('Y-m-d', strtotime($row['P'])) : null,
                    'is_russian' => $row['Q'] === 'Да',
                    'characteristics' => []
                ];
            }

            $variants = [];
            if ($row['I'] !== null and $row['I'] !== "") {
                if (strpos($row['I'], ';') !== false) {
                    $variants = explode('; ', $row['I']);
                } else {
                    $variants = explode(', ', $row['I']);
                }
            }

            $characteristic = [
                $row['F'], // title
                $row['G'], // min
                $row['H'], // max
                implode(', ', $variants), // variants
                $row['J'], // main
                $row['K'], // unit
            ];

            $currentItem['characteristics'][] = $characteristic;

            $count++;
            //if ($count > 10000) break;
        }

        if (!empty($currentItem)) {
            $rows[] = $currentItem;
        }

        $this->logStep("Данные обработаны");

        $result = $rows;
        $jsonResult = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        file_put_contents(__DIR__ . '/result.json', $jsonResult);

        $this->logStep("Результат сохранен в JSON");

        // Добавление элементов в базу данных
        foreach ($rows as $item) {
            $id = $this->add($item);
        }

        $this->logStep(count($rows)." элементов добавлено в базу данных");
    }
    

    public function showUploadForm(): void
    {
        echo <<<HTML
    <form action="" method="post" enctype="multipart/form-data">
        <label for="file">Выберите файл:</label>
        <input type="file" name="file" id="file" required>
        <button type="submit">Загрузить</button>
    </form>
HTML;
    }

    private function getGlobalCategoryKey($category, array &$globalCategories): int
    {
        $key = substr($category, 0, 2);
        $key = intval($key);
        if (!isset($globalCategories[$key])) {
            $globalCategories[$key] = $category;
        }
        return $key;
    }
}