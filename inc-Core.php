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
    
        
        if(isset($_GET['user_id']) && isset($_GET['format'])) {
            
            $userId = $_GET['user_id'];
            $format = $_GET['format'];
            
            $usersModule = new Musers([$this->dbcon, $this->dbconusers]);  
            $usersModule->getUserDataById($userId, $format);
    
            
            exit();
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