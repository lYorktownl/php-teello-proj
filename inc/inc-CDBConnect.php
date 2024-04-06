<?php

class CDBConnect {
    function __construct()
    {
        
    }
    function connect ($fileName){
        $connectData = json_decode(file_get_contents($fileName));
        // print($connectData);
        $dsn = $connectData[0].":host=".$connectData[1].";dbname=".$connectData[2];

        $opt = array (
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );
        return new PDO($dsn, $connectData[3], $connectData[4], $opt);
    }
}