<?php

namespace SimpleTdb;
class TextDataForm
{    
    protected $url;
    protected $act;
    protected $schemItems;
    protected $baseName;

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
        17 => ["Link", "link", "", "link", ""],
        18 => ["scale", "scale", "", "numb", "ruler horizontal"],
        19 => ["File", "file", "", "file", "paperclip"],
        20 => ["Image", "file", "image", "file", "file image outline"],
        21 => ["List text", "list", "text", "arra", "check circle outline"],
        22 => ["List textarea", "list", "textarea", "arra", "check circle outline"],
    ];

    protected array $fieldViewTypes = [
        1 => ["disabled"],
        2 => ["hidden"],
        3 => ["readonly"],
        4 => ["normal"],
        5 => ["required"],
    ];

    public function __construct($url, $schemItems, $baseName='')
    {
        $this->url = $url;
        $this->schemItems = $schemItems;
        $this->baseName = $baseName;
        
    }    


    public function viewForm ($act, $items=[])
    {        

        $html = '
        <h3>Редактирование</h3>
        <form class="ui form" action="'.$this->url.'?act='.$act.'" method="post" id="schemForm">
            <input type="hidden" name="form_info[stage]" value="2">
            <div id="fieldsContainer">
                '.$this->viewFormFields ($items).'                
            </div>

            <div class="field">                
                <button type="submit" class="ui green submit button">Сохранить</button>
            </div>

        </form>
        <script src="/js/async_file_load.js" async></script>
        <script src="/js/heic2any.min.js" async></script>
        ';

        return $html;
    }

    protected function viewFormFields ($items, $html = '')
    {
        
        /*
        foreach ($this->data->all() as $sId => $sInfo){
            if (count($sInfo) == 0) continue;
            if (!is_numeric($sId) ) continue;            

            $html .= $this->viewFormField ($sId, $sInfo);
        }
        */

        foreach ($this->schemItems as $sId => $sInfo){
            $iId = $sInfo[3];

            $itemValue = $items[$iId] ?? '';
            $fValue = $this->getValue ($sInfo, $itemValue);
            //echo $sInfo[2].': ';
            //print_r($fValue);
            //echo '<hr>';
                        
            $html .= $this->viewFormField ($sInfo, $fValue);
        }



        return $html;
        
    }



    public function viewFormField ($schem, $value, $html = '')
    {        
        $typeId = (isset($schem[8]) and $schem[8] != '')? $schem[8] : 1;
        $display = (isset($schem[9]) and $schem[9] != '')? $this->fieldViewTypes[$schem[9]][0] : 4;


        
        $info = $this->fieldTypes[$typeId];

        $primType = $info[1];
        $subType = $info[2];
        $dataType = $info[3];
        $icon = '';
        if (isset($info[4]) and $info[4] != ''){
            $iconClass = $info[4];
            $icon = '<i class="'.$iconClass.' icon"></i> ';
        }

        $fieldId = $schem[3];
        $fieldName = 'form_info['.$schem[3].']';

        $label = $schem[10] ?? $schem[2];
        $description = (isset($schem[12]) and $schem[12] != '')? $schem[12] : "";
        $fieldPlaceholder = $schem[11] ?? "";

        //$listValues = [1=>"Элемент 1", 2=>"Элемент 2", 3=>"Элемент 3",];
        $listValues = $value;
        if ($schem[6] == 'link' or $schem[6] == 'list'){
            $listValues = $schem[17];
            //if (!is_array($listValues)) $listValues = [$listValues];
            $listValues = array_filter($listValues, fn($item) => ($item == '')? false : true);
        }

        //
        if ($schem[6] == 'time'){            
            if ($subType == 'datetime'){
                $value = date('d.m.Y H:i:s', $value);
            } else {
                $value = date('d.m.Y', $value);
            }            
        }


        $wraperProps = [            
            'icon' => $icon ?? '',
            'label' => $label ?? '',
            'description' => $description ?? '',
        ];

        $fieldProps = [
            'id' => $fieldId,
            'name' => $fieldName ?? '',
            'placeholder' => $fieldPlaceholder ?? '',
            'display' => $display,
            'value' => $value ?? '',
            'listValues' => $listValues ?? [],
        ];
        
        $html = $this->genFieldWrapper ($primType, $subType, $wraperProps, $fieldProps, $info);
        
        

        /*
        $funcName = 'gen'.ucfirst($primType);
        $html = $this->$funcName($subType, $fieldId, $fieldName, $value, $fieldPlaceholder, $display, $icon, $label, $description, $listValues, $info);
        
        $field = '<input type="text" name="form_info" value="'.$value.'" '.$placeholder.' '.$display.' />';
        
        if ($display == 'hidden'){
            return $field;
        }
        

        $html = '
        <div class="ui form">
            <div class="field">
                <label>Date</label>
                <div class="ui calendar datetime">
                <div class="ui input left icon">
                    <i class="calendar icon"></i>
                    <input type="text" placeholder="Pick up a date" name="date">
                </div>
                </div>
            </div>
        </div>
        ';
        */
        

        
        return $html;
        
    }


    protected function genFieldWrapper ($primType, $subType, $wraperProps, $fieldProps, $info)
    {
        $funcName = 'gen'.ucfirst($primType).'Field';        
        $field = $this->$funcName($subType, $wraperProps, $fieldProps);

        if ($fieldProps["display"] == 'hidden'){
            return $field;
        }

        $wraperClass = 'field';
        $fieldClass = 'ui input left icon';
        $icon = $wraperProps["icon"] ?? '';

        if ($subType == 'checkbox' or $subType == 'togle') {
            $fieldClass = 'ui input';
            $icon = '';
        }

        if ($primType == 'textarea') {
            $fieldClass = 'ui input';
            $icon = '';
        }

        if ($primType == 'time') {
            $fieldClass = 'ui calendar';
            $icon = '';
        }

        if ($primType == 'time') {
            $fieldClass = 'ui calendar '.$subType;
        }

        if ($primType == 'list') {
            $wraperClass = 'grouped fields';
        }

        if ($primType == 'select') {
            $fieldClass = '';
            $icon = '';
        }

        if ($primType == 'file') {
            $fieldClass = '';
            $icon = '';
        }

        $infoText = '';
        //$infoText = implode(", ", $info);

        $html = '
        <div class="'.$wraperClass.'">            
            <label>'.$wraperProps["label"].'</label>
            '.$infoText.'
            <p>'.$wraperProps["description"].'</p>
            <div class="'.$fieldClass.'">
                '.$icon.'
                '.$field.'
            </div>
        </div>
        ';
        
        return $html;
    }


    protected function genInputField ($subtype, $wraperProps, $fieldProps)
    {
        $type = 'text';
        if ($subtype != '') $type = $subtype;
        $placeholder = ($fieldProps['placeholder'] != '')? ' placeholder="'.$fieldProps['placeholder'].'"' : "";

        $field = '<input type="'.$type.'" name="'.$fieldProps['name'].'" value="'.$fieldProps['value'].'" '.$placeholder.' '.$fieldProps['display'].' />';
        
        if ($subtype == 'checkbox'){
            $field = '
            <label>
                <input type="'.$type.'" name="'.$fieldProps['name'].'" value="'.$fieldProps['value'].'" '.$fieldProps['display'].' />
                '.$fieldProps['placeholder'].'
            </label>';
        }

        /*
        <div class="ui toggle checkbox">
            <input type="checkbox" tabindex="0" class="hidden">
            <label>Toggle</label>
        </div>*/

        if ($subtype == 'togle'){
            $field = '
            <div class="ui toggle checkbox">
                <input type="checkbox" tabindex="0" class="hidden" name="'.$fieldProps['name'].'" value="'.$fieldProps['value'].'" '.$fieldProps['display'].' />
                <label>'.$fieldProps['placeholder'].'</label>
            </div>';
        }


        return $field;
    }

    protected function genTextareaField ($subtype, $wraperProps, $fieldProps)
    {        

        $placeholder =  ($fieldProps['placeholder'] != '')? ' placeholder="'.$fieldProps['placeholder'].'"' : "";
        $field = '<textarea name="'.$fieldProps['name'].'" '.$placeholder.' '.$fieldProps['display'].' >'.$fieldProps['value'].'</textarea>';
        
        return $field;
    }

    protected function genTimeField ($subtype, $wraperProps, $fieldProps)
    {        

        $placeholder = ($fieldProps['placeholder'] != '')? ' placeholder="'.$fieldProps['placeholder'].'"' : "";
        $field = '<input type="text" name="'.$fieldProps['name'].'" value="'.$fieldProps['value'].'" '.$placeholder.' '.$fieldProps['display'].' autocomplete="off"/>';
              

        $html = '
        <div class="ui input left icon">
            '.$wraperProps['icon'].'
            '.$field.'
        </div>
        ';

        /*

        $class = $subtype ?? '';

        $html = '
        <div class="field">            
            <label>'.$label.'</label>
            '.implode(", ", $info).'
            <p>'.$description.'</p>
            <div class="ui calendar '.$class.'">
                <div class="ui input left icon">
                    '.$icon.'
                    '.$field.'
                </div>
            </div>
        </div>
        ';
        */
        
        return $html;
    }

    protected function genListField ($subType, $wraperProps, $fieldProps)
    {        
        
        $placeholder = ($fieldProps['placeholder'] != '')? ' placeholder="'.$fieldProps['placeholder'].'"' : "";

        $type = "radio";
        if ($subType != '') $type = $subType;
        
        $listItems = '';
        foreach ($fieldProps["listValues"] as $id => $name){
            if ($subType == 'checkbox' || $subType == 'radio') {

                $field = '<input type="'.$type.'" name="'.$fieldProps["name"].'[]" value="'.$id.'" '.$placeholder.' '.$fieldProps["display"].' /> '. $name;
            
            } else if ($subType == 'text') {

                $field = '<input type="'.$type.'" name="'.$fieldProps["name"].'['.$id.']" value="'.$name.'" '.$placeholder.' '.$fieldProps["display"].' />';
            /*
            } else if ($subType == 'textarea') {
                $field = '<textarea name="'.$fieldProps["name"].'['.$id.']" '.$placeholder.' '.$display.'>'.$name.'</textarea>';
            */

            }


            $listItems .= '
            <div class="field">
                <label>
                    '.$field.'                    
                </label>
            </div>';
        }
        
        return $listItems;
        
        /*
        if ($fieldProps["display"] == 'hidden'){
            return $listItems;
        }

        $html = '
        <div class="grouped fields">            
            <label>'.$label.'</label>
            <p>'.implode(", ", $info).'</p>
            <p>'.$description.'</p>
            '.$listItems.'
            
        </div>        
        ';
        
        return $html;
        */
    }

    protected function genSelectField ($subType, $wraperProps, $fieldProps, $info=[])
    {        
        
        
        $multiple = "";
        if ($subType == 'multiple') {
            $multiple = 'multiple';
            $fieldProps["name"] .= '[]';
        }
        
        $listItems = ($fieldProps['placeholder'] != '')? '<option value="">'. $fieldProps['placeholder'].'</option>' : "";
        $fieldProps['value'] = (is_array($fieldProps['value'])) ? $fieldProps['value'] : [$fieldProps['value']];

        foreach ($fieldProps["listValues"] as $id => $info){
            $name = $info[0] ?? '';
            $selected = '';
            if (in_array($id, $fieldProps['value'])) $selected = ' selected';
            $listItems .= '<option value="'.$id.'" '.$selected.'>'. $name.'</option>';
        }        
        
        if ($fieldProps["display"] == 'hidden'){
            return '';
        }

        $html = '
        <select name="'.$fieldProps["name"].'" class="ui dropdown search" '.$multiple.'>
            '.$listItems.'
        </select>';

        /*

        $html = '
        <div class="field">            
            <label>'.$label.'</label>
            <p>'.implode(", ", $info).'</p>
            <p>'.$description.'</p>
            <select name="'.$fieldName.'" class="ui dropdown" '.$multiple.'>
            '.$listItems.'
            </select>
        </div>
        ';
        */
        
        return $html;
    }

    protected function genFileField ($subType, $wraperProps, $fieldProps, $info=[])
    {
        //print_r($listValues);

        $listItems = '';
        foreach ($fieldProps["listValues"] as $id => $value){
            $listItems .= '
            <div class="ui clearing segment file-item" style="background: none;">
                <a class="ui right floated tiny blue icon button removeFiles" title="Удалить файл">
                <i class="trash icon"></i></a>
                '.$value.'
                <input type="hidden" name="form_info['.$fieldProps["id"].'][]" value="'.$id.'">
            </div>            
            ';
        }
        
        $html = '
            <div class="inline fields">
                <div class="field">                    
                    <div class="ui left icon input">
                        <i class="file upload icon"></i>
                        <input class="fileInput" type="file" name="form_info[files][]" placeholder="" value="" multiple
                            data-fieldid="'.$fieldProps["id"].'" data-basename="'.$this->baseName.'" data-itemid=""
                        />
                    </div>
                </div>           
            </div>
            <div class="ui segments fileList">
                '.$listItems.'
            </div>
        ';
        
        return $html;
    }












    protected function genInput ($subtype, $fieldId, $fieldName, $value, $placeholder, $display, $icon, $label, $description, $listValues=[], $info=[])
    {
        
        $type = 'text';
        if ($subtype != '') $type = $subtype;
        $placeholder = ($placeholder != '')? ' placeholder="'.$placeholder.'"' : "";
        if (is_array($value)){
            $_SESSION['message'][] = ['war', 'Для колонки ['.$label.'] значение массив вместо строки'];
            $value = implode (", ", $value);
        }

        $field = '<input type="'.$type.'" name="'.$fieldName.'" value="'.$value.'" '.$placeholder.' '.$display.' />';
        
        if ($display == 'hidden'){
            return $field;
        }

        $html = '
        <div class="field">            
            <label>'.$label.'</label>
            '.implode(", ", $info).'
            <p>'.$description.'</p>
            <div class="ui input left icon">
                '.$icon.'
                '.$field.'
            </div>
        </div>
        ';
        
        return $html;
    }

    protected function genTextarea ($subtype, $fieldId, $fieldName, $value, $placeholder, $display, $icon, $label, $description, $listValues=[], $info=[])
    {        

        $placeholder = ($placeholder != '')? ' placeholder="'.$placeholder.'"' : "";
        $field = '<textarea name="'.$fieldName.'" value="'.$value.'" '.$placeholder.' '.$display.' >'.$value.'</textarea>';
        
        if ($display == 'hidden'){
            return $field;
        }

        $html = '
        <div class="field">            
            <label>'.$label.'</label>
            '.implode(", ", $info).'
            <p>'.$description.'</p>
            '.$field.'
        </div>
        ';
        
        return $html;
    }

    protected function genTime ($subtype, $fieldId, $fieldName, $value, $placeholder, $display, $icon, $label, $description, $listValues=[], $info=[])
    {        

        $placeholder = ($placeholder != '')? ' placeholder="'.$placeholder.'"' : "";
        $field = '<input type="text" name="'.$fieldName.'" value="'.$value.'" '.$placeholder.' '.$display.' autocomplete="off"/>';
        $class = $subtype ?? '';
        
        if ($display == 'hidden'){
            return $field;
        }

        $html = '
        <div class="field">            
            <label>'.$label.'</label>
            '.implode(", ", $info).'
            <p>'.$description.'</p>
            <div class="ui calendar '.$class.'">
                <div class="ui input left icon">
                    '.$icon.'
                    '.$field.'
                </div>
            </div>
        </div>
        ';
        
        return $html;
    }

    protected function genList ($subType, $fieldId, $fieldName, $value, $placeholder, $display, $icon, $label, $description, $listValues=[], $info=[])
    {        
        
        $placeholder = ($placeholder != '')? ' placeholder="'.$placeholder[11].'"' : "";
        $type = "radio";
        if ($subType != '') $type = $subType;
        
        $listItems = '';
        foreach ($listValues as $id => $name){
            if ($subType == 'checkbox' || $subType == 'radio') {

                $field = '
                <input type="'.$type.'" name="'.$fieldName.'[]" value="'.$id.'" '.$placeholder.' '.$display.' /> '. $name;

            } else if ($subType == 'text') {

                $field = '<input type="'.$type.'" name="'.$fieldName.'['.$id.']" value="'.$name.'" '.$placeholder.' '.$display.' />';
            
            } else if ($subType == 'textarea') {
                $field = '<textarea name="'.$fieldName.'['.$id.']" '.$placeholder.' '.$display.'>'.$name.'</textarea>';
            }


            $listItems .= '
            <div class="field">
                <label>
                    '.$field.'                    
                </label>
            </div>';
        }        
        
        if ($display == 'hidden'){
            return $listItems;
        }

        $html = '
        <div class="grouped fields">            
            <label>'.$label.'</label>
            <p>'.implode(", ", $info).'</p>
            <p>'.$description.'</p>
            '.$listItems.'
            
        </div>        
        ';
        
        return $html;
    }

    protected function genSelect ($subType, $fieldId, $fieldName, $value, $placeholder, $display, $icon, $label, $description, $listValues=[], $info=[])
    {        
        
        
        $multiple = "";
        if ($subType == 'multiple') {
            $multiple = 'multiple';
            $fieldName .= '[]';
        }
        
        $listItems = ($placeholder != '')? '<option value="">'. $placeholder.'</option>' : "";
        $selectedItems = '';

        //print_r($listValues);
        //echo '<br>';
        if (is_array($value) and count($value) != 0){
            foreach ($value as $cureVal){
                $name = $listValues[$cureVal][0] ?? $cureVal;
                $selectedItems .= '<option value="'.$cureVal.'" selected>'. $name.'</option>';
            }
        }

        foreach ($listValues as $id => $cureVal){
            $name = $cureVal[0] ?? '';
            if (in_array($id, $value)) continue;
            $listItems .= '<option value="'.$id.'" >'. $name.'</option>';
        }        
        
        if ($display == 'hidden'){
            return '';
        }

        $html = '
        <div class="field">            
            <label>'.$label.'</label>
            <p>'.implode(", ", $info).'</p>
            <p>'.$description.'</p>
            <select name="'.$fieldName.'" class="ui search dropdown" '.$multiple.'>
            '.$selectedItems.'
            '.$listItems.'
            </select>
        </div>
        ';
        
        return $html;
    }

    protected function genFile ($subType, $fieldId, $fieldName, $value, $placeholder, $display, $icon, $label, $description, $listValues=[], $info=[])
    {
        //print_r($listValues);

        $listItems = '';
        foreach ($listValues as $id => $value){
            $listItems .= '
            <div class="ui clearing segment file-item" style="background: none;">
                <a class="ui right floated tiny blue icon button removeFiles" title="Удалить файл">
                <i class="trash icon"></i></a>
                '.$value.'
                <input type="hidden" name="form_info['.$fieldId.'][]" value="'.$value.'">
            </div>            
            ';
        }
        
        $html = '
        <div class="field">
            <label>'.$label.'</label>
            <div class="inline fields">
                <div class="field">                    
                    <div class="ui left icon input">
                        <i class="file upload icon"></i>
                        <input class="fileInput" type="file" name="form_info[files][]" placeholder="" value="" multiple
                            data-fieldid="'.$fieldId.'" data-basename="'.$this->baseName.'" data-itemid=""
                        />
                    </div>
                </div>           
            </div>
            <div class="ui segments fileList">
                '.$listItems.'
            </div>         
        </div>
        ';
        
        return $html;
    }










    protected function getValue ($schemItem, $item)
    {
        $result = '';
        $type = $schemItem[6];       
        //print_r($type);
        //echo '<br>';

        if ($type == 'arra' or $type == 'file' or $type == 'link'){
            if (!is_array($item)){
                $item = ($item == '') ? [] : [$item]; 
            }
        }
        
        $result = $item;

        return $result;
    }

    
}
