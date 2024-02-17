<?php

class CTable {
    private $inner;
    private $cover;
    private $params;
    private $header;

    function __construct(){
        $this->params ="";
        $this->inner="";
        $this->header="";
    }

    function adatrib($name, $value){
        $this->params.=" $name = \"$value\"";
    }

    function addrow($tr){
        $this->inner.=$tr;
    }

    function addhdr($tr) {
        $this->header =$tr;
    }

    function out(){
        $str= $this->cover;
        $str=str_replace("[params]", $this->params, $str);
        $str=str_replace("[params]", $this->header, $str);
        $str=str_replace("[inner]", $this->inner, $str);
        return $str;
    }
}