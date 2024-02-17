<?php
class Musers extends MBaseModule
{
	
	function execute()
	{
    	if (isset($_GET['edituser'])) {
        	$this->editUser();
    	} else {
        	$this->showUsers();
    	}
	}
	function editUser(){
		$usersObj = new Tusers($this->dbcon);
		$this->content = '<h1>Редактирование</h1>';
		$userId = $_GET['edituser'];
			
			if ($usersObj->select($userId)){

				$this->selflink.='&edituser='.$userId;

				$itemInfo = [];
				$itemInfo['name'] = $usersObj->getinfo('name');
				$itemInfo['login'] = $usersObj->getinfo('login');
				$itemInfo['email'] = $usersObj->getinfo('email');


				if (isset($_POST['saveuser'])){
					$itemInfo['name'] = $_POST['name'];
					$itemInfo['login'] = $_POST['login'];
					$itemInfo['email'] = $_POST['email'];

					if($usersObj->setinfo($itemInfo)){
						header('Location: '.$this->selflink.'&savesuccess');
					} else {
					$this->content.='<div class="errors">Ошибка сохранения</div>';
					}
				}
				
				$this->content.='<form method="post" action="'.$this->selflink.'">';
				$this->content.='<div><input type ="text" name = "name" value =" '.$itemInfo['name'].'">';
				$this->content.='<div><input type ="text" name = "login" value =" '.$itemInfo['login'].'">';
				$this->content.='<div><input type ="text" name = "email" value =" '.$itemInfo['email'].'">';
				$this->content.='<div><input type ="submit"
					name = "saveuser" value ="Сохранить">';
				$this->content.='</form>';
				
				$this->content.='<div><a href="/?module=users">Назад</div>';
					
				

			}
			
			else{
				$this->content.='<div>Данные не найдены</div>';
				header('Location: /');
			}
		}
    
    function showUsers(){
		 
		$usersObj = new Tusers($this->dbcon);

        $this->content ='<h1>Пользователи</h1>';
		$this->content.='<div><a href= "'.$this->selflink.'&adduser"> Добавить пользователя</div>';
		
		if(isset($_GET['adduser'])){
			$usersObj->create(['name'=>'новый']);
			header('Location: '.$this->selflink);
		}
		if(isset($_GET['deleteuser'])){
			
			$uid = $_GET['deleteuser'];
			if ($usersObj->select($uid)) {
				$usersObj->setinfo(['del'=>1]);
			}
			header('Location: '.$this->selflink);
		}

		$userList = $usersObj->getList();
		
		foreach ($userList as $key => $value) {
			$usersObj->select($value['id']);
			$linkEdit = '<a href="' . $this->selflink . '&edituser=' . $value['id'] . '">[Редактировать]</a>';  // Fix the variable name
			$linkDelete = '<a href="' . $this->selflink . '&deleteuser=' . $value['id'] . '">[Удалить]</a>';  // Fix the variable name
			$this->content .= '<div>' . $usersObj->getinfo('name') . ' ' . $linkEdit . ' ' . $linkDelete . '</div>';
		}     
		$this->content.='<div><a href="?">назад</div>';   
    }
}
