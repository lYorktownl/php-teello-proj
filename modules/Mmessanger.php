<?php
class Mmessanger extends MBaseModule
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
            $linkSendMessage = '<a class="btn btn-primary" href="?module=messanger&sendmessage=' . $value['id'] . '">Отправить сообщение</a>';
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

        if (isset($_GET['sendmessage'])) {
            $recipientId = $_GET['sendmessage'];
            
            $usersObj = new Tusers($this->dbconusers);
            if ($usersObj->select($recipientId)) {
                $this->content = '<h1>Отправка сообщения</h1>';
                
                if (isset($_POST['send'])) {
                    $itemInfo['title'] = $_POST['title'];
                    $itemInfo['descr'] = $_POST['descr'];
                   
                    $messageObj = new Tmessages($this->dbconmessages);
                    $messageObj->create(['sender_id' => $currentUser_id, 'recipient_id' => $recipientId, 'title' => $itemInfo['title'], 'descr' => $itemInfo['descr']]);
                    header('Location: ?module=messanger'); 
                } else {
                    $this->content .= '<form method="post" action="?module=messanger&sendmessage=' . $recipientId . '">';
                    $this->content .= '<input type="text" name="title" placeholder="Тема сообщения"><br>';
                    $this->content .= '<textarea name="descr" placeholder="Текст сообщения"></textarea><br>';
                    $this->content .= '<input type="submit" name="send" value="Отправить">';
                    $this->content .= '</form>';
                }
            } else {
                $this->content = '<div>Пользователь не найден</div>';
            }
        }
    }
}