<?php
abstract class Tbasemodel {
    protected $dbcon;
    protected $resourse;

    function __construct($con)
    {
        $this->dbcon = $con;
    }

    function getList(){
        $qwry = $this->dbcon->query('select `id` from `'.static::$tblname.'` where `del` = 0');
        return $qwry->fetchAll();
    }

    function select ($itemid) {
        $qwry = $this->dbcon->prepare("select * from `".static::$tblname."` where `id`=? and `del`=0");
        $qwry->execute(array($itemid));
        if ($this->resourse=$qwry->fetch()) {
            return 1;
        }
        return 0;
    }

    function selectBy($values){
        $ssql = "";
        $ssql2 ="";
        $sep= '';
        foreach ($values as $param => $val) {
            $ssql.=" and `".$param."` = :".$param;
        }
       
        $qwry = $this->dbcon->prepare("select * from `".static::$tblname."` where `del`=0".$ssql);
        $qwry->execute($values);
        
        if ($this->resourse=$qwry->fetch()) {
            return 1;
        }
        return 0;
    }

    function setinfo($values){
        $ssql = "";
        $sep= '';
        foreach ($values as $param => $val) {
            $this->resourse[$param] =$val;
            $ssql.=$sep."`".$param."` = :". $param;
            $sep = ", ";
        }
        $values["id"]=$this->resourse["id"];
        $qwrysvs = $this->dbcon->prepare("UPDATE `".static::$tblname."` set".$ssql." where `del`=0 and `id`= :id");
        $rs = $qwrysvs->execute($values);
        return $rs;
    }
    
    function create($values){
        $ssql1 = "";
        $ssql2 = "";
        $sep= "";
        foreach ($values as $param => $val) {
            $ssql1.=$sep. "`".$param."`";
            $ssql2.=$sep. ":".$param;
            $sep= ", ";
        }
        try {
            $this->dbcon->beginTransaction();
            $qwry=$this->dbcon->prepare("INSERT INTO `".static::$tblname."` (".$ssql1.") values (".$ssql2.")");
            $rs = $qwry->execute($values);
            $qwry = $this->dbcon->query("SELECT MAX(`id`) as `id` FROM `" . static::$tblname . "`");
            $row = $qwry->fetch();
            $res = $row["id"];
            $this->dbcon->commit();
            return $res;
        } catch(PDOException $e) {
            $this->dbcon->rollBack();
            return 0;
        }
    }

    function getinfo($param){
        return $this->resourse[$param];
    }

}