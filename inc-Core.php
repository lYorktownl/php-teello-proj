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
	function editUser(){
		$this->content.='<h1>Редактирование</h1>';
		$userId = $_GET['edituser'];
		$qwry = $this->dbcon->prepare('SELECT * FROM `users` WHERE `id` = ?');
			
		$qwry->execute([$userId]);
			
		if ($row = $qwry->fetch()) {
			if (isset($_POST['saveuser'])) {
				$newname = $_POST['name'];
				$newemail = $_POST['email'];
				$qwrysave = $this->dbcon->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
				$rs = $qwrysave->execute([$newname, $newemail, $userId]);
	
				if ($rs) {
					header('Location: /?edituser=' . $userId . '&savesuccess');
				} else {
					$this->content.='<div class="errors">Ошибка сохранения</div>';
				}
			}
	
			$this->content .= '<form method="post">';
			$this->content .= '<div><label for="name">Имя:</label><input type="text" name="name" value="' . $row['name'] . '"></div>';
			$this->content .= '<div><label for="email">Email:</label><input type="text" name="email" value="' . $row['email'] . '"></div>';
			$this->content .= '<div><input type="submit" name="saveuser" value="Сохранить"></div>';
			$this->content .= '</form>';
	
			$this->content.='<div><a href="/">Назад</div>';
		} else {
			$this->content.='<div>Данные не найдены</div>';
			header('Location: /');
		}
	}
	
    
    function showUsers(){
        $this->content.='<h1>Пользователи</h1>';
		$this->content.='<div><a href= "?adduser"> Добавить пользователя</div>';
		
		if (isset($_GET['adduser'])) {
			$qwry = $this->dbcon->query('INSERT INTO `users` (`name`) VALUES (\'Новый пользователь\')');
			header('Location: /');
		}
		if(isset($_GET['deleteuser'])){
			
			$uid = $_GET['deleteuser'];
			$qwry = $this->dbcon->prepare('select* from `users` where `id`= ?');
			$qwry->execute([$uid]);
			if ($row=$qwry->fetch()){
				$qwrysave = $this->dbcon->prepare('update `users` set `del`= 1 where `id` = ?');
			$rs = $qwrysave->execute([$uid]);
			}
			header('Location: /');
		}
	

        $qwry = $this->dbcon->query('select * from `users` where `del` = 0');

        while ($row = $qwry->fetch()){
            //print_r($row);
			
			$linkEdit = '<a href="?edituser=' . $row['id'] . '">[Редактировать]</a>';
			$linkDelete = '<a href="?deleteuser=' . $row['id'] . '">[Удалить]</a>';
			$this->content.='<div>'.$row['name'].' '.$linkEdit.' '.$linkDelete.'</div>';
			
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