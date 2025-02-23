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
	function editUser() {
		$usersObj = new Tusers($this->dbconusers);
		$tasksusersObj = new Ttasksusers($this->dbcon);
		$tasksObj = new Ttasks($this->dbcon);
		$authObj = new CUserAuth($this->dbconusers);
	
		$this->content = '<h1>Редактирование пользователя</h1>';
		$userId = $_GET['edituser'];
	
		// Проверяем, авторизован ли пользователь
		$isAdmin = false;
		$currentUserId = null;
	
		if ($authObj->checkAuth()) {
			$currentUserId = $authObj->getUserId();
			$usersObj->select($currentUserId);
			$isAdmin = ($usersObj->getinfo('role') === 'admin');
		}
	
		// Проверяем, может ли пользователь редактировать этот профиль
		if (!$isAdmin && $currentUserId != $userId) {
			$this->content .= '<div class="alert alert-danger">У вас нет прав для редактирования этого профиля.</div>';
			return;
		}
	
		if ($usersObj->select($userId)) {
			$this->selflink .= '&edituser=' . $userId;
	
			$itemInfo = [];
			$itemInfo['name'] = $usersObj->getinfo('name');
			$itemInfo['login'] = $usersObj->getinfo('login');
			$itemInfo['email'] = $usersObj->getinfo('email');
			$itemInfo['password'] = $usersObj->getinfo('password');
	
			if (isset($_POST['saveuser'])) {
				$itemInfo['name'] = $_POST['name'];
				$itemInfo['login'] = $_POST['login'];
				$itemInfo['email'] = $_POST['email'];
				$itemInfo['password'] = $_POST['password'];
	
				// Обработка загрузки фото
				if ($_FILES['photo']['error'] == 0) {
					$tt = explode('/', $_FILES['photo']['type']);
					if ($tt[1] == 'jpeg' || $tt[1] == 'png') {
						$newfilename = md5($userId . date('Y-m-d H:i:s') . rand()) . '.' . $tt[1];
						if (move_uploaded_file($_FILES['photo']['tmp_name'], 'photos/' . $newfilename)) {
							$itemInfo['photo'] = $newfilename;
						}
					}
				}
	
				if ($usersObj->setinfo($itemInfo)) {
					header('Location: ' . $this->selflink . '&savesuccess');
				} else {
					$this->content .= '<div class="errors">Ошибка сохранения</div>';
				}
			}
	
			// Форма редактирования
			$this->content .= '<form method="post" action="' . $this->selflink . '" enctype="multipart/form-data">';
			$this->content .= '<div class="w-fit">';
			$this->content .= '<div>Имя: <input class="form-control" type="text" placeholder="Имя" name="name" value="' . $itemInfo['name'] . '"></div>';
			$this->content .= '<div>Email: <input class="form-control" type="text" placeholder="Email" name="email" value="' . $itemInfo['email'] . '"></div>';
			$this->content .= '<div>Логин: <input class="form-control" type="text" placeholder="Логин" name="login" value="' . $itemInfo['login'] . '"></div>';
			$this->content .= '<div>Пароль: <input class="form-control" type="text" placeholder="Пароль" name="password" value="' . $itemInfo['password'] . '"></div>';
			if (!empty($itemInfo['photo'])) {
				$photoURL = 'photos/' . $itemInfo['photo'];
				$this->content .= '<img src="' . $photoURL . '" alt="Avatar">';
			} else {
				$this->content .= '<div>Фото: <input class="mt-3" type="file" name="photo"></div>';
			}
			$this->content .= '<div><input class="btn m-3" type="submit" name="saveuser" value="Сохранить"></div>';
			$this->content .= '</div>';
			$this->content .= '</form>';
	
			// Список задач пользователя
			$userTasks = $tasksusersObj->getListBy(['userid' => $userId]);
			if (!empty($userTasks)) {
				$this->content .= '<h2>Список задач пользователя</h2>';
				$this->content .= '<ul>';
				foreach ($userTasks as $task) {
					$tasksusersObj->select($task['id']);
					$taskId = $tasksusersObj->getinfo('taskid');
					$tasksObj->select($taskId);
					$this->content .= '<li>' . $tasksObj->getinfo('header') . '</li>';
				}
				$this->content .= '</ul>';
			} else {
				$this->content .= '<div>Нет задач</div>';
			}
	
			// Кнопка "Назад"
			$this->content .= '<div><a class="btn btn-sucsess" href="/?module=users"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
				<path stroke="none" d="M0 0h24v24H0z" fill="none" />
				<path d="M5 12l14 0" />
				<path d="M5 12l6 6" />
				<path d="M5 12l6 -6" />
			  </svg>Назад</a></div>';
		} else {
			$this->content .= '<div>Данные не найдены</div>';
			header('Location: /');
		}
	}

		function showUsers() {
			$usersObj = new Tusers($this->dbconusers);
			$authObj = new CUserAuth($this->dbconusers);
		
			$this->content = '<h1>Пользователи</h1>';
		
			// Проверяем, авторизован ли пользователь
			$isAdmin = false;
			$currentUserId = null;
		
			if ($authObj->checkAuth()) {
				$currentUserId = $authObj->getUserId();
				$usersObj->select($currentUserId);
				$isAdmin = ($usersObj->getinfo('role') === 'admin');
			}
		
			// Кнопка "Добавить пользователя" только для администратора
			if ($isAdmin) {
				$this->content .= '<div class="btn btn-sucsess bnt-pill"><a href="' . $this->selflink . '&adduser">Добавить пользователя <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg></a></div>';
			}
		
			// Обработка добавления пользователя
			if (isset($_GET['adduser']) && $isAdmin) {
				$usersObj->create(['name' => 'новый']);
				header('Location: ' . $this->selflink);
			}
		
			// Обработка удаления пользователя
			if (isset($_GET['deleteuser']) && $isAdmin) {
				$uid = $_GET['deleteuser'];
				if ($usersObj->select($uid)) {
					$usersObj->setinfo(['del' => 1]);
				}
				header('Location: ' . $this->selflink);
			}
		
			// Список пользователей
			$userList = $usersObj->getList();
			foreach ($userList as $key => $value) {
				$usersObj->select($value['id']);
				$this->content .= '<div class="row fw-bold" style="grid-rows: auto; display: grid;">';
				$this->content .= '<div class="names col-6">' . $usersObj->getinfo('name') . '</div>';
		
				// Кнопки "Редактировать" и "Удалить" только для администратора или текущего пользователя
				if ($isAdmin || $currentUserId == $value['id']) {
					$linkEdit = '<a class="btn btn-primary" href="' . $this->selflink . '&edituser=' . $value['id'] . '">Редактировать</a>';
					$linkDelete = '<a class="btn btn-danger" href="' . $this->selflink . '&deleteuser=' . $value['id'] . '">Удалить</a>';
					$this->content .= '<div class="buttons">' . $linkEdit . ' ' . $linkDelete . '</div>';
				}
		
				$this->content .= '</div>';
			}
		
			// Кнопка "Назад"
			$this->content .= '<div><a class="btn btn-sucsess my-2" href="?"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
				<path stroke="none" d="M0 0h24v24H0z" fill="none" />
				<path d="M5 12l14 0" />
				<path d="M5 12l6 6" />
				<path d="M5 12l6 -6" />
			  </svg>назад</a></div>';
		}	
}
