<?php
 class MBaseModule {
    protected $dbcon;
	protected $content;
	
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