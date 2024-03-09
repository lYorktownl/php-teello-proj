
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
    
        $cnt = count($this->cells); 
        $this->cells[$cnt] = "<th $params>$value</th>"; 
    }

    function addparam($name, $value){
    
        $this->params.=" $name = \"Svalue\"";
    }

    function addcell($value, $params=""){

        $cnt = count($this->cells); 
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