<?php
class Mmessages extends MBaseModule
{
    function execute()
	{
    	if (isset($_GET['sendmessage'])) {
        	$this->sendMessage();
    	} else {
        	$this->showUsers();
    	}
	}
    function showUsers()
    {
        $usersObj = new Tusers($this->dbconusers);
        $userList = $usersObj->getList(); 
        $this->content = '<h1>Мессенджер</h1>';

        foreach ($userList as $key => $value) {
            $usersObj->select($value['id']);
            $linkSendMessage = '<a class="btn btn-primary" href="?module=messages&sendmessage=' . $value['id'] . '">Отправить сообщение</a>';
            $this->content .= '<div>' . '<div>' . $usersObj->getinfo('name') . '</div>' . '<div>' . $linkSendMessage . '</div>';
        }
        $this->content .= '<div><a class="btn btn-success" href="?"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M5 12l14 0" />
        <path d="M5 12l6 6" />
        <path d="M5 12l6 -6" />
      </svg>Назад</a></div>';
    }

    
    function sendMessage()
    {
        $userId = $_SESSION['userid'];
        if (isset($_GET['sendmessage'])) {
            $recipientId = $_GET['sendmessage'];
            
            $usersObj = new Tusers($this->dbconusers);
            if ($usersObj->select($recipientId)) {
                $this->content = '<h1>Отправка сообщения</h1>';
                
                if (isset($_POST['send'])) {
                    $itemInfo['title'] = $_POST['title'];
                    $itemInfo['descr'] = $_POST['descr'];
                    $messageObj = new Tmessages($this->dbcon); 
                    $messageObj->create(['senderId' => $userId, 'recipientId' => $recipientId, 'title' => $itemInfo['title'], 'descr' => $itemInfo['descr']]);
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
                    // header('Location:?module=messages'); 
                } else {
                    // Вывод формы для отправки сообщения
                    $this->content .= '<form method="post" action="?module=messages&sendmessage=' . $recipientId . '" enctype="multipart/form-data">';
                    $this->content .= '<input class="form-control" type="text" name="title" placeholder="Тема сообщения"><br>';
                    $this->content .= '<textarea class="form-control" name="descr" placeholder="Текст сообщения"></textarea><br>';
                    $this->content.='<div>Photo <input type ="file"  name = "photo">';
                    $this->content .= '<input class="btn" type="submit" name="send" value="Отправить">';
                    $this->content .= '</form>';

                    // Вывод отправленных сообщений
                    $this->content .= '<h2>Отправленные сообщения:</h2>';
                    $messageObj = new Tmessages($this->dbcon); 
                    $sentMessages = $messageObj->getListBy(['senderId' => $userId]); 
                    
                    if (!empty($sentMessages)) {
                        $this->content .= '<ul>';
                        foreach ($sentMessages as $message => $value) {
                            $messageObj->select($value['id']);
                            $this->content .= '<li>';
                            $this->content .= 'Тема: ' . $messageObj->getinfo('title');
                            $this->content .= '<br>';
                            $this->content .= 'Текст: ' . $messageObj->getinfo('descr');
                            $this->content .= $messageObj->getinfo('photo');
                            $this->content .= '</li>';
                        }                        
                        $this->content .= '</ul>';
                    } else {
                        $this->content .= '<p>Нет отправленных сообщений</p>';
                    }
                }
            } else {
                $this->content = '<div>Пользователь не найден</div>';
            }
            $this->content.='<div><a class="btn btn-sucsess" href="?module=messages"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
		<path stroke="none" d="M0 0h24v24H0z" fill="none" />
		<path d="M5 12l14 0" />
		<path d="M5 12l6 6" />
		<path d="M5 12l6 -6" />
	  </svg>назад</a></div>'; 
        }
    }
}