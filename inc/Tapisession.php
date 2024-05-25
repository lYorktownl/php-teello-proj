<?php

class Tapisession extends Tbasemodel {
    protected static $tblname ='api_session';

    public function __construct($con)
    {
        parent::__construct($con);
        $dt = new datetime;
        $expSes = $con->prepare("update `".static::$tblname."` set `del`=1  where `datetill`<? and `del`=0");
        $expSes->execute([$dt->format('Y-m-d H:i:s')]);
    }
}