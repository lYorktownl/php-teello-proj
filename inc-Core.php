<?php 

class Core {
    private $content;
    private $tmpl;
    private $dbcon;
    private $dbconusers;
   
    private $userName;

    function __construct(){
        
        // $this->dbconnect();
    }
    function execute() {
        $connector= new CDBConnect;
        $this->dbcon = $connector->connect('connect.dat');

        $this->dbconusers = $connector->connect('connect2.dat');

        $this->tmpl = file_get_contents('tmpl/page.html');
        
        $usersObj = new Tusers($this->dbconusers);
        $authObj = new CUserAuth($this->dbconusers);
    
        if ($authObj->checkAuth()) {
            $uid = $authObj->getUserId();
            $usersObj->select($uid);
            $this->userName = $usersObj->getinfo('name');
            $this->router();
        } else {
            $this->tmpl = file_get_contents('tabler-dev/demo/sign-in.html');
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
			$module = new $objName ([$this->dbcon, $this->dbconusers]);
			$module -> execute();
			$this->content = $module->getContent();
		}
	}	
	

    function dbconnect (){
        $connector = new CDBConnect;
        $this->dbcon = $connector->connect('connect.dat');
        // $type = 'mysql';
        // $host = 'localhost';
        // $base = 'new_user';
        // $user = 'root';
        // $pasw = '';

        // $this->hidemode = 0;

        // $dsn = $type.":host=".$host.";dbname=".$base;
        // $opt = array (
        //     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        //     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        // );

        // $this->dbcon = new PDO($dsn, $user, $pasw, $opt);

        $stmt = $this->dbcon->query('SET NAMES utf8');
    }

    function build(){
        $page = $this->tmpl;
        $page = str_replace('{[content]}',$this->content, $page);
        $page = str_replace('{[title]}','our page',$page);
        $page = str_replace('{[user_name]}',$this->userName,$page);
    
        print ($page);
    }
}