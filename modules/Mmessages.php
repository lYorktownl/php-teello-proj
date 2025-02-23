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

        foreach ($userList as $value) {
            $usersObj->select($value['id']);
            $linkSendMessage = '<a class="btn btn-primary" href="?module=messages&sendmessage=' . $value['id'] . '">Чат</a>';
            $this->content .= '<div class="mb-3">' .
                '<div>' . $usersObj->getinfo('name') . ' ' . $this->getUserStatus($value['id']) . '</div>' .
                '<div>' . $linkSendMessage . '</div></div>';
        }
    }

    function sendMessage()
    {
        error_log("sendMessage() called with recipientId: " . $_GET['sendmessage']);
        $userId = $_SESSION['userid'];
        $recipientId = $_GET['sendmessage'] ?? 0;
        if (!is_numeric($recipientId) || $recipientId <= 0) {
            $this->content = '<div>Некорректный идентификатор пользователя</div>';
            return;
        }
        
        $usersObj = new Tusers($this->dbconusers);
        if ($usersObj->select($recipientId)) {
            $this->content = '<h1>Чат с ' . htmlspecialchars($usersObj->getinfo('name')) . '</h1>';
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
                $title = trim($_POST['title'] ?? '');
                $descr = trim($_POST['descr'] ?? '');
                
                if (empty($title) || empty($descr)) {
                    $this->content .= '<p class="text-danger">Тема и текст сообщения не могут быть пустыми.</p>';
                } else {
                    $messageData = [
                        'senderId' => $userId,
                        'recipientId' => $recipientId,
                        'title' => $title,
                        'descr' => $descr,
                        'photo' => $this->handleFileUpload(),
                        'status' => 0,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    $messageObj = new Tmessages($this->dbcon);
                    error_log("Message data: " . print_r($messageData, true));
                    if ($messageObj->create($messageData)) {
                        error_log("Ошибка: сообщение не сохранено. SQL ошибка: " . $this->dbcon->error);
                        header('Location:?module=messages&sendmessage=' . $recipientId);
                        exit;
                    } else {
                        $this->content .= '<p class="text-danger">Ошибка отправки сообщения.</p>';
                    }
                }
            }
            
            $this->content .= $this->renderChatForm($recipientId);
            $this->content .= $this->renderMessages($userId, $recipientId);
        } else {
            $this->content = '<div>Пользователь не найден</div>';
        }
    }

    private function handleFileUpload()
    {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
            return '';
        }
        
        $fileType = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            return '';
        }
        
        $newFileName = md5(time() . rand()) . '.' . $fileType;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], 'photos/' . $newFileName)) {
            return $newFileName;
        }
        
        return '';
    }

    private function renderChatForm($recipientId)
    {
        return '<form method="post" action="?module=messages&sendmessage=' . $recipientId . '" enctype="multipart/form-data">'
            . '<div class="mb-3"><input class="form-control" type="text" name="title" placeholder="Тема сообщения"></div>'
            . '<div class="mb-3"><textarea class="form-control" name="descr" placeholder="Текст сообщения"></textarea></div>'
            . '<div class="mb-3">Фото: <input type="file" name="photo" class="form-control"></div>'
            . '<div class="mb-3"><input class="btn btn-primary" type="submit" name="send" value="Отправить"></div>'
            . '</form>';
    }

    private function renderMessages($userId, $recipientId)
    {
        $messageObj = new Tmessages($this->dbcon);
        $messages = $messageObj->getListBy(['recipientId' => [$userId, $recipientId], 'senderId' => [$userId, $recipientId]], 'timestamp ASC');
        
        $output = '<h2>Сообщения:</h2><div class="chat-container">';
        foreach ($messages as $msg) {
            $messageObj->select($msg['id']);
            $isSender = $messageObj->getinfo('senderId') == $userId;
            $messageClass = $isSender ? 'sent-message' : 'received-message';
            $output .= '<div class="message ' . $messageClass . '">';
            $output .= '<strong>' . htmlspecialchars($messageObj->getinfo('title')) . ':</strong><br>';
            $output .= htmlspecialchars($messageObj->getinfo('descr'));
            if ($photo = $messageObj->getinfo('photo')) {
                $output .= '<br><img src="photos/' . htmlspecialchars($photo) . '" class="img-fluid mt-2" style="max-width: 200px;">';
            }
            $output .= '</div>';
        }
        $output .= '</div>';
        return $output;
    }
    
    private function getUserStatus($userId)
    {
        return '<span class="badge bg-success">Online</span>'; // Заглушка, нужно реализовать систему статусов
    }
}

// CSS стили для чата
echo '<style>
.chat-container { display: flex; flex-direction: column; gap: 10px; }
.message { max-width: 60%; padding: 10px; border-radius: 10px; }
.sent-message { align-self: flex-end; background-color: #d1e7ff; }
.received-message { align-self: flex-start; background-color: #f1f1f1; }
</style>';
?>
