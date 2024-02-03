<?php
 class MBaseModule {
    private $dbcon;
	private $content;
	
	function __construct($con)
	{
	$this->dbcon = $con;
	}

    function execute (){

    }

    function getContent()
	{
		return $this->content;
	}

}