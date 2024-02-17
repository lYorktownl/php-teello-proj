<?php
abstract class Tbasemodel {
    protected $dbcon;
    protected $resource;

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
        if ($this->resource=$qwry->fetch()) {
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
        
        if ($this->resource=$qwry->fetch()) {
            return 1;
        }
        return 0;
    }

    function getListBy($values,$params=[]){
        $ssql = "";
        $ssql2 ="";
        $sep= '';
        foreach ($values as $param => $val) {
            if (is_array($val)){
                $sval ="";
                $sep="";

                foreach ($val as $key =>$vl){
                    $sval.=$sep."$vl";
                    $sep=", "; 
                }

                unset($values[$param]);
                $ssql.=" and `".$param."` in ($sval)";

            } elseif ($val[0]=="b") {
                //between 
                unset($values[$param]);
                $vv=explode("|",$val);

                $sparam = "s".$param;
                $tparam = "t".$param;

                $values[$sparam]=$vv[1];
                $values[$tparam]=$vv[2];

                $ssql.=" and` ".$param."` >= :".$sparam." and `".$param."` <= :".$tparam;
            } 
            elseif ($val[0]=="<") {
                if ($val[1]=="=") {
                    $ssql.=" and `".$param."` <= :".$param;
                    $values[$param]=str_replace("<=","",$values[$param]);
                } else {
                    $ssql.=" and `".$param."` < :".$param;
                    $values[$param]=str_replace("<","",$values[$param]);
                }
            }
            elseif ($val[0]==">") {
                if ($val[1]=="=") {
                    $ssql.=" and `".$param."` >= :".$param;
                    $values[$param]=str_replace(">=","",$values[$param]);
                } else {
                    $ssql.=" and `".$param."` > :".$param;
                    $values[$param]=str_replace(">","",$values[$param]);
                }
            }
            elseif ($val[0]=="%") {
                $ssql.=" and `".$param."` like :".$param;
            } 
            else {
                $ssql.=" and `".$param."` = :".$param;
            }
        }

        $fieldlist="`id`";

        if ($params) {
            if (isset($params["orderby"])) {
                $orderby = $params["orderby"];
                if (strpos($orderby, 'description')>0 || strpos($orderby,'asc')>0) {
                    $ssql.=' order by '.$orderby;
                } else {
                    $ssql.=' order by `'.$orderby.'`';
                }
            }
            if (isset($params["limit"])) {
                $ssql.=" limit ".$params["limit"];
            }
            if (isset($params["distinct"])) {
                $fieldlist = "distinct(`id`)";
            }
            if (isset($params["fields"])) {
                $fieldlist = $params["fields"];
            }
        }
        
        if (isset($params["trace"])) {
            print (static::$tblname);
            print_r($params);
            print_r($values);
        }

        $qwry = $this->dbcon->prepare("select ".$fieldlist." from `".static::$tblname."` where `del`=0".$ssql);
        if (isset($params["trace"])) {       
            print_r($qwry);
        }
        $qwry->execute($values);
        return $qwry->fetchAll();
    }      

    function setinfo($values){
        $ssql = "";
        $sep= "";
        foreach ($values as $param => $val) {
            $this->resource[$param] =$val;
            $ssql.=$sep."`".$param."` = :". $param;
            $sep = ", ";
        }
        $values["id"]=$this->resource["id"];
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
        return $this->resource[$param];
    }

}