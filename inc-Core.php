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
        $connector = new CDBConnect;
        $this->dbcon = $connector->connect('connect.dat');
        $this->dbconusers = $connector->connect('connect2.dat');
    
        $this->tmpl = file_get_contents('tmpl/page.html');
    
        $usersObj = new Tusers($this->dbconusers);
        $authObj = new CUserAuth($this->dbconusers);
    
        // Проверяем авторизацию
        if ($authObj->checkAuth()) {
            $uid = $authObj->getUserId();
            $usersObj->select($uid);
            $this->userName = $usersObj->getinfo('name');
        }
    
        // Всегда вызываем роутер, даже для неавторизованных пользователей
        $this->router();
    
        // Если пользователь не авторизован и не на странице регистрации, показываем страницу входа
        if (!$authObj->checkAuth() && (!isset($_GET['module']) || $_GET['module'] !== 'register')) {
            $this->tmpl = file_get_contents('tabler-dev/demo/sign-in.html');
        }
    
        $this->build();
    }
	function router() {
        $moduleName = 'main';
        if (isset($_GET['module'])) {
            $moduleName = $_GET['module'];
        }
    
        // Обработка модуля регистрации
        if ($moduleName === 'register') {
            $module = new Mregister([$this->dbcon, $this->dbconusers]);
            $module->execute();
            $this->content = $module->getContent();
            return;
        }
    
        // Обработка запросов для авторизованных пользователей
        $modulesObj = new Tmodules($this->dbcon);
        if ($modulesObj->selectBy(['name' => $moduleName])) {
            $objName = $modulesObj->getinfo('object');
            $module = new $objName([$this->dbcon, $this->dbconusers]);
            $module->execute();
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