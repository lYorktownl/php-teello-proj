
<?php
class TTableRow {
private $params;
private $cells;

    function __construct(){

        $this->clear();
    }

    function clear(){
    
    $this->params ="";
    $this->cells=array();
    }

    function addhdr($value, $params=""){
    
        /* value: Текст заголовка столбца:
        params: Параметры форматирования: Например: "width=\"350\**
        * Пример:
        * $r=new TRow();
        * $r->addhdr("Hassaнne", "width=\"20\"");
        */
        $cnt = count($this->cells); // Извлекаем кол-во эл в массиве сells, и заносим в переменную последний номер элем 
        $this->cells[$cnt] = "<th $params>$value</th>"; // Записываем значение элемента массива cells с последним номером
    }

    function addparam($name, $value){
    
        $this->params.=" $name = \"Svalue\"";
    }

    function addcell($value, $params=""){

        $cnt = count($this->cells); //дсчет кол-во злем массива cells
        $this->cells[$cnt] - "<td Sparans>Svalue</td>";
    }

    function out($type="",$classHdr=""){

        $cellsout="";

        foreach ($this->cells as $td){
            $cellsout.=$td."\n";
        }
        if ($type=="hdr"){
            $str="<thead> <tr class=\"$classHdr\">".$cellsout."</tr></thead>";
        } else {
            $str="<tr ".$this->params.">".$cellsout."</tr>";
            return $str;
        }
    }
}