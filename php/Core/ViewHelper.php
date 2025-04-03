<?php

class ViewHelper {   

    public function showTable ($cols, $rows, $foot=[])
    {
        $head_text = $row_text = $foot_text = '';
        foreach ($cols as $c_id => $c_name){
            $head_text .= '<th>'.$c_name.'</th>';
        }
        $head_text = '<thead><tr>'.$head_text.'</tr></thead>';

        foreach ($rows as $r_id => $r_info){
            $cell_text = '';

            $row_answers = [];
            foreach ($cols as $c_id => $c_name){
                $val = (isset($r_info[$c_id]))? $r_info[$c_id] : '';
                $cell_text .= '<td>'.$val.'</td>';
                if (is_numeric($val)) $row_answers[] = $val;
            }

            //$row_class = (array_sum($row_answers) == 0) ? 'class="ui warning"': '';
            $row_class = '';
            $row_text .= '<tr '.$row_class.'>'.$cell_text.'</tr>';
        }

        if (count($foot) > 0){
            foreach ($cols as $c_id => $c_name){
                $val = (isset($foot[$c_id]))? $foot[$c_id] : '';
                $foot_text .= '<td>'.$val.'</td>';
            }
        }

        $html = '        
        <table class="ui compact table">
            '.$head_text.'
            '.$row_text.'
            '.$foot_text.'
        </table>';

        return $html;

    }
    
        


}

