<?php

namespace SimpleTdb;

use SimpleTdb\TextDataBase as TDB;


class TextDataSchemForm
{    

    protected array $dataSysTypes = [
        1 => ['Обычный'],
        2 => ['Системный'],
    ];

    protected array $dataUnic = [
        1 => ['Должен быть уникальным'],
    ];

    protected array $mainTypes = [
        "numb" => ['Число'],
        "text" => ['Текст'],
        "bool" => ['Чекбокс'],
        "time" => ['Время'],
        "arra" => ['Массив'],
        "list" => ['Список'],
        "link" => ['Связь'],
        "file" => ['Файл'],
        "grup" => ['Группа'],
    ];

    protected array $subTypes = [
        "numb" => [
            0 => 'int',
            1 => 'float',
            2 => 'fileSize',
        ],
    ];

    protected array $fieldTypes = [
        1 => ["Single line Text", "input", "text", "text", "paragraph"],        
        2 => ["Number", "input", "number", "numb", "calculator"],
        3 => ["Password", "input", "password", "text", "asterisk"],
        4 => ["Long Text", "textarea", "", "text", "align justify"],
        5 => ["Long Text Formate", "textarea", "wisiwig", "text", "file alternate outline"],
        6 => ["Checkbox", "input", "checkbox", "bool", "square outline"],
        7 => ["Toggle", "input", "togle", "bool", "toggle on"],
        8 => ["Date", "time", "date", "time", "calendar alternate outline"],
        9 => ["Time", "time", "time", "time", "clock outline"],
        10 => ["Date and Time", "time", "datetime", "time", "calendar plus outline"],
        11 => ["Duration", "time", "duration", "time", "hourglass outline"],
        12 => ["Calendar", "time", "calendar", "time", ""],
        11 => ["List Radio", "list", "radio", "arra", "check circle outline"],
        12 => ["List Checkbox", "list", "checkbox", "arra", "check square outline"],
        13 => ["Select", "select", "", "arra", "list ul"],
        14 => ["Multiple Select", "select", "multiple", "arra", "tasks"],
        15 => ["Group", "group", "", "grup", "layer group"],
        16 => ["List text", "list", "text", "arra", "layer group"],
        17 => ["Link", "link", "", "link", ""], //
        18 => ["scale", "scale", "", "numb", "ruler horizontal"],
        19 => ["File", "file", "", "file", "paperclip"],
        20 => ["Image", "file", "image", "file", "file image outline"],
        21 => ["List text", "list", "text", "arra", ""], //
        22 => ["List textarea", "list", "textarea", "arra", ""],
    ];

    protected array $fieldViewTypes = [
        1 => ["disabled"],
        2 => ["hidden"],
        3 => ["readonly"],
        4 => ["normal"],
        5 => ["required"],
    ];

    protected $schem;
    protected $modelName;
    protected $modelUrl;

    protected $modelsSelect;
    protected $modelsSchemSelect;

    public function __construct($schem, $modelName, $modelUrl)
    {
        $this->schem = $schem;
        $this->modelName = $modelName;
        $this->modelUrl = $modelUrl;
        


        //Формируем поля для формы редактирования
        $modelsSelect = $modelsSchemSelect = [];
        foreach ($this->schem->getModels() as $key => $model){            
            $modelsSelect[$key] = [$key];

            foreach ($model->schem->getSchem() as $sid => $sInfo){
                $itemName = $sInfo[10] ?? $sInfo[2];
                $modelsSchemSelect[$key][$sid] = [$itemName];
            }
        }

        $this->modelsSelect = $modelsSelect;
        $this->modelsSchemSelect = $modelsSchemSelect;

    }

    public function viewForm ()
    {
        $defaultValues = ['','','','','','','','','','','','','','','','',['','','',''] ,[]];

        $html = '
        <h3>Редактирование схемы базы ['.$this->modelName.']</h3>
        <form class="ui form" action="'.$this->modelUrl.'?act=schem" method="post" id="schemForm">
            <input type="hidden" name="form_info[stage]" value="2">
            <div id="fieldsContainer">
                '.$this->viewSchemFields ().'
                
            </div>

            <div class="tfield">
                <button type="button" class="ui blue button" id="addFieldButton">Добавить поле</button>
                <button type="submit" class="ui green submit button">Сохранить</button>
            </div>

        </form>

        <script type="text/javascript" charset="utf-8">
            function createNewFieldHTML(index) {
                return `'.$this->viewSchemField ('${index}', $defaultValues).'`;
            }
        </script>';

        return $html;
    }

    public function viewSchemFields ($html = '')
    {

        foreach ($this->schem->getSchem() as $sId => $sInfo){
            if (count($sInfo) == 0) continue;
            if (!is_numeric($sId) ) continue;

            //print_r($sInfo);
            //echo '<br>';

            $type = $sInfo[6];
            $selectedBase = '';
            
            
            if (!is_array($sInfo[15])) $sInfo[15] = [];
            $sInfo[15] = implode (', ', $sInfo[15]);

            if (!is_array($sInfo[17])) $sInfo[17] = [];
            //$sInfo[17] = implode (', ', $sInfo[17]);

            //print_r($values[16]);
            if (!is_array($sInfo[16])) $sInfo[16] = [];
            $sInfo[16][0] = $sInfo[16][0] ?? '';
            $sInfo[16][1] = $sInfo[16][1] ?? [];
            $sInfo[16][2] = $sInfo[16][2] ?? '';
            $sInfo[16][3] = $sInfo[16][3] ?? '';


            if ($type == 'link' or $type == 'file'){
                if (!is_array($sInfo[16][1])) $sInfo[16][1] = [$sInfo[16][1]];
                $selectedBase = $sInfo[16][0];
                //print_r($sInfo);
                //echo '<hr>';
                
            }
            

            $html .= $this->viewSchemField ($sId, $sInfo, $selectedBase);
        }

        return $html;
        
    }



    public function viewSchemField ($id, $values=[], $selectedBase='')
    {
        
        $dataSysTypeName = 'form_info['.$id.'][4]';
        $dataUnicName = 'form_info['.$id.'][5]';
        $mainTypeName = 'form_info['.$id.'][6]';
        $subTypeName = 'form_info['.$id.'][7]';
        $fieldTypeName = 'form_info['.$id.'][8]';
        $fieldViewTypeName = 'form_info['.$id.'][9]';

        $modelsSelectName = 'form_info['.$id.'][16][0]';
        $modelsSchemSelectName = 'form_info['.$id.'][16][1]';


        //Формируем отображение link

        
        $linkField = '';
        if ($values[6] == 'link' or $values[6] == 'file'){

            $modelFields = '';
            if ($selectedBase != ''){
                $modelFields = $this->getFormSelect (
                    $this->modelsSchemSelect[$selectedBase],
                    $modelsSchemSelectName,
                    $values[16][1],
                    [],
                    'Поля для отображения'
                );
                $modelFields .= '<input type="text" name="form_info['.$id.'][16][2]" placeholder="ID колонки в схеме" value="'.$values[16][2].'" readonly>';
            }

            $linkField = '
            <div class="inline field">
                <label>Связь с другой базой</label>
                '.$this->getFormSelect ($this->modelsSelect, $modelsSelectName, $values[16][0], [], 'Выберите базу').'                
                '.$modelFields.'                
            </div>';

        }

        //Формируем отображение list
        //$listField = '<input type="text" name="form_info['.$id.'][17]" placeholder="Элементы" value="'.$values[17].'" >';
        $listField = '';
        if (is_array($values[17])){
            $values[17] = array_filter($values[17], fn($item) => ($item == '')? false : true);
            if (count($values[17]) > 0){
                foreach ($values[17] as $varId => $varInfo){
                    $listField .= '
                    <div class="inline field">
                        <label>'.$varId.':</label>
                        <input type="text" name="form_info['.$id.'][17]['.$varId.'][0]" placeholder="Элементы" value="'.$varInfo[0].'" >
                    </div>';
                }
                $listField = '
                <div class="field">
                    <label>Элементы</label>
                    '.$listField.'
                </div>';
            }

        }



        //print_r($values);
        //echo '<br>';

        //echo $selectedBase.'<br>';
        
        

        $html = '
        <div class="form-item" data-index="'.$id.'">
            <div class="inline fields" data-index="'.$id.'">
                <div class="field">
                    <input type="text" name="form_info['.$id.'][2]" placeholder="Название колонки" value="'.$values[2].'">
                </div>                

                <div class="field">
                    '.$this->getFormSelect ($this->mainTypes, $mainTypeName, $values[6], [], 'Выберите основной тип').'
                </div>

                <div class="inline field">
                    '.$this->getFormSelect ($this->fieldTypes, $fieldTypeName, $values[8], [], 'Отображение поля').'
                </div>

                

                <div class="field">
                    <a type="button" class="ui blue icon button showHide" href="#schem_block_'.$id.'"><i class="cog icon"></i></a>
                </div>

                <div class="field">
                    <button type="button" class="ui red icon button removeField"><i class="trash alternate icon"></i></button>
                </div>
            </div>
            <div class="hidden schem-props" id="schem_block_'.$id.'">
                
                <div class="inline field">
                    <label>id колонки</label>
                    <input type="text" name="form_info['.$id.'][0]" placeholder="id колонки" value="'.$values[0].'" readonly>
                </div>
                <div class="inline field">
                    <label>Инкремент</label>
                    <input type="text" name="form_info['.$id.'][3]" placeholder="Инкремент" value="'.$values[3].'" readonly>
                </div>

                <div class="inline field">
                    <label>Системное поле</label>
                    '.$this->getFormSelect ($this->dataSysTypes, $dataSysTypeName, $values[4], [], '').'
                </div>

                <div class="inline field">
                    <label>Уникальность</label>
                    '.$this->getFormSelect ($this->dataUnic, $dataUnicName, $values[5], [], 'Может повторятся').'
                </div>

                <div class="inline field">
                    <label>Подтип</label>
                    '.$this->getFormSelect ($this->subTypes, $subTypeName, $values[7], [], 'Выберите подтип').'
                </div>

                <div class="inline field">
                    <label>Тип поля</label>
                    '.$this->getFormSelect ($this->fieldViewTypes, $fieldViewTypeName, $values[9], [], 'Тип поля').'
                </div>

                <div class="inline field">
                    <label>Имя поля</label>
                    <input type="text" name="form_info['.$id.'][10]" placeholder="Имя поля" value="'.$values[10].'" >
                </div>
                <div class="inline field">
                    <label>Подсказка</label>
                    <input type="text" name="form_info['.$id.'][11]" placeholder="Подсказка" value="'.$values[11].'" >
                </div>
                <div class="inline field">
                    <label>Описание</label>
                    <input type="text" name="form_info['.$id.'][12]" placeholder="Описание" value="'.$values[12].'" >
                </div>

                <div class="inline field">
                    <label>Порядок отображения</label>
                    <input type="text" name="form_info['.$id.'][13]" placeholder="Порядок отображения" value="'.$values[13].'" >
                </div>
                <div class="inline field">
                    <label>Значение по умолчанию</label>
                    <input type="text" name="form_info['.$id.'][14]" placeholder="Значение по умолчанию" value="'.$values[14].'" >
                </div>

                <div class="inline field">
                    <label>Свойства поля</label>
                    <input type="text" name="form_info['.$id.'][15]" placeholder="Свойства поля" value="'.$values[15].'" >
                </div>
                '.$linkField.'
                '.$listField.'
            </div>
        </div>
            ';        
        
        return $html;
    }

    protected function getFormSelect ($items, $fieldName, $cureVal='', $classes=[], $emptyName=null)
    {        
        $multiple = "";
        if (is_array($cureVal)) {
            $classes[] = 'ui dropdown';
            $multiple = 'multiple';
            $fieldName .= '[]';
        }
        
        
        $startOptName = $emptyName ?? 'Не выбрано';

        $opt_html = '<option value="">'.$startOptName.'</option>';
        $opt_selected = '';

        
        $cureVal = (is_array($cureVal))? $cureVal : [$cureVal];
        $cureVal = array_diff($cureVal, [""]);
        
        

        if (count($cureVal) > 0){
            foreach ($cureVal as $id){
                $name = $items[$id][0] ?? $id;
                $opt_selected .= '<option value="'.$id.'" selected>'.$name.'</option>';
            }
            
        }
        foreach ($items as $value => $info){
            if (count($cureVal) > 0 and in_array($value, $cureVal)){
                continue;
            }
            $opt_html .= '<option value="'.$value.'">'.$info[0].'</option>';
        }

        $html = '
        <select name="'.$fieldName.'" class="'.implode(" ", $classes).'" '.$multiple.'>
            '.$opt_selected.'
            '.$opt_html.'
        </select>
        ';
        return $html;
    }
    
}
