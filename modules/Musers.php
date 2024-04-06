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
		$usersObj = new Tusers($this->dbconusers);
		$tasksusersObj = new Ttasksusers($this->dbcon);
		$tasksObj = new Ttasks($this->dbcon);
		$this->content = '<h1>Редактирование</h1>';
		$userId = $_GET['edituser'];
			
			if ($usersObj->select($userId)){

				$this->selflink.='&edituser='.$userId;

				$itemInfo = [];
				$itemInfo['name'] = $usersObj->getinfo('name');
				$itemInfo['login'] = $usersObj->getinfo('login');
				$itemInfo['email'] = $usersObj->getinfo('email');
				$itemInfo['password'] = $usersObj->getinfo('password');



				if (isset($_POST['saveuser'])){
					$itemInfo['name'] = $_POST['name'];
					$itemInfo['login'] = $_POST['login'];
					$itemInfo['email'] = $_POST['email'];
					$itemInfo['password'] = $_POST['password'];
					print_r($_FILES);
					$fname=$_FILES['photo']['name'];
					$ferror=$_FILES['photo']['error'];
					$tmpl=$_FILES['photo']['tmp_name'];
					$ftype=$_FILES['photo']['type'];

					if ($ferror==0){
						$tt=explode('/',$ftype);
						print_r($tt);
						if($tt[1]=='jpeg' || $tt[1]=='png'){

						$newfilename = md5($userId.date('Y-m-d H:i:s').rand()).'.'.$tt[1];
							if (move_uploaded_file($tmpl, 'photos/'.$newfilename)){
								$itemInfo['photo']=$newfilename;
							}
						}
					}
					

					if($usersObj->setinfo($itemInfo)){
						// header('Location: '.$this->selflink.'&savesuccess');
					} else {
					$this->content.='<div class="errors">Ошибка сохранения</div>';
					}
				}
				
				$this->content.='<form method="post" action="'.$this->selflink.'" enctype="multipart/form-data">';
				$this->content.='<div><input type ="text" placeholder="name" name = "name" value =" '.$itemInfo['name'].'">';
				$this->content.='<div><input type ="text" placeholder="login" name = "login" value =" '.$itemInfo['login'].'">';
				$this->content.='<div><input type ="text" placeholder="email" name = "email" value =" '.$itemInfo['email'].'">';
				$this->content.='<div><input type ="text" placeholder="password" name = "password" value =" '.$itemInfo['password'].'">';
				$this->content.='<div>Photo <input type ="file"  name = "photo"';
				$this->content.='<div><input type ="submit"
					name = "saveuser" value ="Сохранить">';
				$this->content.='</form>';
				
				
					
				
				$userTasks = $tasksusersObj->getListBy(['userid' => $userId]);

				if (!empty($userTasks)) {
					$this->content .= '<h2>Список задач пользователя</h2>';
					$this->content .= '<ul>';
					foreach ($userTasks as $task) {
						$tasksusersObj->select($task['id']);
						$taskId = $tasksusersObj->getinfo('taskid');
						$tasksObj->select($taskId);
						$this->content .= '<li>' . $tasksObj->getinfo('header') . '</li>' ;
					}
					$this->content .= '</ul>';
				} else {
					$this->content .= '<div>Нет задач</div>';
				}
				$this->content .= '<div><a class="btn btn-sucsess" href="/?module=users"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
				<path stroke="none" d="M0 0h24v24H0z" fill="none" />
				<path d="M5 12l14 0" />
				<path d="M5 12l6 6" />
				<path d="M5 12l6 -6" />
			  </svg>Назад</a></div>';
			}else{
				$this->content.='<div>Данные не найдены</div>';
				header('Location: /');
			}
		}
		
    function showUsers(){
		 
		$usersObj = new Tusers($this->dbconusers);

        $this->content ='<h1>Юзвери</h1>';
		$this->content.='<div class="btn btn-sucsess bnt-pill"><a href= "'.$this->selflink.'&adduser"> Добавить пользователя  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg></div>';
		
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
			$linkEdit = '<a class="btn btn-primary" href="' . $this->selflink . '&edituser=' . $value['id'] . '">Edit</a>';  
			$linkDelete = '<a class="btn btn-danger" href="' . $this->selflink . '&deleteuser=' . $value['id'] . '">Delete</a>';  
			$this->content .= '<div class="row" style="grid-rows: auto; display: grid;">'.'<div class="names col-6">' . $usersObj->getinfo('name') . '</div>' .'<div class="buttons">' . $linkEdit . ' ' . $linkDelete . '</div>';
		}     
		$this->content.='<div><a class="btn btn-sucsess" href="?"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
		<path stroke="none" d="M0 0h24v24H0z" fill="none" />
		<path d="M5 12l14 0" />
		<path d="M5 12l6 6" />
		<path d="M5 12l6 -6" />
	  </svg>назад</a></div>';   
    }
}
