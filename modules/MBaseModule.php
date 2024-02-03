<?php
 class MBaseModule {
    protected $dbcon;
	protected $content;
	protected $selflink;
	
	function __construct($con)
	{
	$this->dbcon = $con;
	$this->selflink = '?module='.$_GET['module'];
	}

    function execute (){

    }

    function getContent()
	{
		return $this->content;
	}

}