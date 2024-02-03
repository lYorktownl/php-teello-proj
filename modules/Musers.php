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
				if (isset($_POST['saveuser'])){
					$newname = $_POST['name'];
					$newemail = $_POST['email'];
					if($usersObj->setinfo(['name' => $newname, 'email' => $newemail])){
						header('Location: /?edituser=' .$userId.'&savesuccess');
					} else {
					$this->content.='<div class="errors">Ошибка сохранения</div>';
					}
				}
				
				$this->content.='<form method="post">';
				$this->content.='<div><input type ="text" name = "name" value =" '.$usersObj->getinfo('name').'">';
				$this->content.='<div><input type ="text"
					name = "email" value =" '.$usersObj->getinfo('email').'">';
				$this->content.='<div><input type ="submit"
					name = "saveuser" value ="Сохранить">';
				$this->content.='</form>';
				
				$this->content.='<div><a href="/">Назад</div>';
					
			}
			
			else{
				$this->content.='<div>Данные не найдены</div>';
				//$this->content.='<div><a href="/">Назад</div>';
				header('Location: /');
			}
			
			
		}
    
    function showUsers(){
		 
		$usersObj = new Tusers($this->dbcon);

        $this->content ='<h1>Пользователи</h1>';
		$this->content.='<div><a href= "?adduser"> Добавить пользователя</div>';
		
		if(isset($_GET['adduser'])){
			$usersObj->create(['name'=>'новый']);
			header('Location: /');
			
		}
		if(isset($_GET['deleteuser'])){
			
			$uid = $_GET['deleteuser'];
			if ($usersObj->select($uid)) {
				$usersObj->setinfo(['del'=>1]);
			}
			header('Location: /');
		}

		$userList = $usersObj->getList();
		//print_r($uList);
		
		foreach ($userList as $key => $value) {
			$usersObj->select($value['id']);
			$linkEdit = '<a href= "?edituser='.$value['id'].'">[Редактировать]</a>';
			$linkEdit = '<a href= "?deleteuser='.$value['id'].'">[Удалить]</a>';
			$this->content.='<div>'.$usersObj->getinfo('name').' '.$linkEdit.' '.$linkDel. '</div>';
		}

        // $qwry = $this->dbcon->query('select * from `users` where `del` = 0');

    //     while ($row = $qwry->fetch()){
    //         //print_r($row);
			
	// 		$linkEdit = '<a href= "?edituser='.$row['id'].'">[Редактировать]</a>';
	// 		$linkEdit = '<a href= "?deleteuser='.$row['id'].'">[Удалить]</a>';
	// 		$this->content.='<div>'.$row['name'].' '.$linkEdit.'</div>';
			
    //     }
    }
	
	
	
}
