<?php
 class MBaseModule {
    protected $dbcon;
	protected $dbconusers;
	protected $content;
	protected $selflink;
	
	function __construct($cons=[])
	{
	$this->dbcon = $cons[0];
	$this->dbconusers = $cons[1];
	$this->selflink = '?';
	if (isset($_GET['module']))
	$this->selflink='?module='.$_GET['module'];
	}

    function execute (){

    }

    function getContent()
	{
		return $this->content;
	}

}