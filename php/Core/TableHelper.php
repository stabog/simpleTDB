<?php

class TableHelper{

    protected $cols = [];
    protected $rows = [];
    protected $props = [];


    public function __construct($cols,  $rows, $props=[])
    {
        $this->cols = $cols;
        $this->rows = $rows;
        $this->props = $props;
    }

    public function viewTable()
    {
        

        //$this->props["url"]

        $html = '

    <div class="ui scrolling fluid container table-wrapper">
        <table class="ui first head stuck unstackable compact celled table">
            <thead>
            '.$this->viewTableCols().'
            </thead>
            <tbody>
            '.$this->viewTableRows().'
            </tbody>
        </table>
    </div>';

        return $html;
    }

    public function viewTableCols()
    {
        $url = $this->props["url"] ?? '';
        
        $cols_html = '<th>#</th>';
        foreach ($this->cols as $cId => $cInfo){
            $cName = $cInfo[0];
            
            $sortOrd = 0;
            $sortIcon = '';
            if (isset($this->props["sort"]["id"]) and $this->props["sort"]["id"] == $cId) {                
                $sortIcon = ($this->props["sort"]["ord"] == 0) ? '<i class="sort amount down alternate icon"></i> ' : '<i class="sort amount down icon"></i> ';
                $sortOrd = ($this->props["sort"]["ord"] == 0) ? 1 : 0;                
            }            
            $sort_url = '&sort[0][id]='.$cId.'&sort[0][ord]='.$sortOrd;

            $cols_html .= '
            <th>
                '.$sortIcon.'
                <a href="'.$url. $sort_url.'">'.$cName.'</a>
            </th>';
        }
        $cols_html = '<tr>'.$cols_html.'</tr>';

        return $cols_html;
    }

    public function viewTableRows()
    {
        $rows_html = '';
        $rows_count = 0;
        foreach ($this->rows as $r_id => $rInfo){
            $rows_count ++;
            $row_html = '
            <td>
                '.$rows_count.'
                <a class=""ui tiny icon button" href="?act=form&id='.$r_id.'"><i class="pen icon"></i></a>
                <a class=""ui tiny icon button" href="?act=del&id='.$r_id.'"><i class="trash icon"></i></a>
                </td>';
            
            foreach ($rInfo as $itemId => $itemInfo){
                $itemText =  $itemInfo[0];
                $row_html .= '<td>'.$itemText.'</td>';
            }
            $rows_html .= '<tr>'.$row_html.'</tr>';


        }

        return $rows_html;
    }
}
