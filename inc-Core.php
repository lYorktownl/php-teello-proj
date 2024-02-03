<?php 

class Core {
    private $content;
    private $dbcon;

    function __construct(){
        
        $this->dbconnect();
    }
    function execute () {

       $authObj = new CUserAuth($this->dbcon);

       if ($authObj->checkAuth()) {
        // include("modules/Musers.php");

        // $module = new Musers($this->dbcon);
        // $module->execute();
        // $this->content = $module->getContent();
		$this->router();
       } else{
        $this->content = file_get_contents('tmpl/authForm.html');
       }
       $this->build();
    }
	function router (){
		$moduleName = 'main';
		if (isset($_GET['module'])) {
			$moduleName = $_GET['module'];
		}
		$modulesObj = new Tmodules($this->dbcon);
		if ($modulesObj->selectBy(['name'=>$moduleName])) {
			$objName = $modulesObj->getinfo('object');
			$module = new $objName ($this->dbcon);
			$module -> execute();
			$this->content = $module->getContent();
		}
	}	
	

    function dbconnect (){

        $type = 'mysql';
        $host = 'localhost';
        $base = 'new_user';
        $user = 'root';
        $pasw = '';

        $this->hidemode = 0;

        $dsn = $type.":host=".$host.";dbname=".$base;
        $opt = array (
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );

        $this->dbcon = new PDO($dsn, $user, $pasw, $opt);

        $stmt = $this->dbcon->query('SET NAMES utf8');
    }

    function build(){
        $page = file_get_contents ('./page.html');
        $page = str_replace('{[content]}',$this->content, $page);
        $page = str_replace('{[title]}','our page',$page);
    
        print ($page);
    }
}