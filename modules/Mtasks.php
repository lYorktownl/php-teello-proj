<?php
class Mtasks extends MBaseModule {

    function execute()
    {
        $this->handleCommentSubmission();
        if (isset($_GET['edittask'])) {
            $this->editTask();
        } else {
            $this->showTasks();
        }
    }

    function editTask(){

        // echo "<pre>Session data: ";
        // print_r($_SESSION);
        // echo "</pre>";
    
        // echo "<pre>GET data: ";
        // print_r($_GET);
        // echo "</pre>";
    
        // echo "<pre>POST data: ";
        // print_r($_POST);
        // echo "</pre>";

        $tasksObj = new Ttasks($this->dbcon);
        $usersObj = new Tusers($this->dbconusers); 
        $stateObj = new Tstate($this->dbcon);
        $tasksusersObj = new Ttasksusers($this->dbcon);

        $this->content = '<h1 style="width: fit-content">Редактирование задачи</h1>';
        $taskId = $_GET['edittask'];
        
        $baseLink = $this->selflink;

        if ($tasksObj->select($taskId)){
            $this->selflink.='&edittask='.$taskId;
            $itemInfo =[];
            $itemInfo['header']=$tasksObj->getinfo('header');
            $itemInfo['description']=$tasksObj->getinfo('description');
            if (isset($_POST['savetask'])){
                
                $itemInfo['header'] = $_POST['header'];
                $itemInfo ['description'] = $_POST['description'];
                if($tasksObj->setinfo($itemInfo)){
                    header('Location: '.$this->selflink.'&savesuccess');
                } else {
                    $this->content.='<div class="errors">Ошибка сохранения</div>';
                }
            }
            
            if (isset($_GET['start'])) {
                $tasksObj->setinfo(['state'=>1, 'dstart'=>date('Y-m-d H:i:s')]);
                header('Location: '.$this->selflink.'');
            }
            if(isset($_GET['finish'])) {
                $tasksObj->setinfo(['state'=>2, 'dfinish'=>date('Y-m-d H:i:s')]);
                header('Location: '.$this->selflink.'');
            }

            $stateObj->select($tasksObj->getinfo('state'));
            $usersObj->select($tasksObj->getinfo('owner'));
            $dt = new datetime($tasksObj->getinfo('dcreate'));

            $this->content.='<div style="width: fit-content">Автор: '.$usersObj->getinfo('name').'</div>';
            $this->content.='<div style="width: fit-content">Создана: '.$dt->format('d.m.Y H:i').'</div>';
            $this->content.='<div style="width: fit-content">Состояние: '.$stateObj->getinfo('name').'</div>';
            if ($tasksObj->getinfo('state')==0) {
                $this->content.='<div style="width: fit-content"><a href="'.$this->selflink.'&edittask='.$taskId.'&start"><button class="btn btn-success">Начать задачу</button></a></div>';
                
            }
            if ($tasksObj->getinfo('state')>0) {
                $dt = new datetime($tasksObj->getinfo('dcreate'));
                $this->content.='<div style="width: fit-content">Запущена: '.$dt->format('d.m.Y H:i').'</div>';
            }
            if ($tasksObj->getinfo('state')==1) {
                $this->content.='<div style="width: fit-content"><a href="'.$this->selflink.'&edittask='.$taskId.'&finish"><button class="btn btn-success">Завершить задачу</button></a></div>';
            }
            if ($tasksObj->getinfo('state')>1) {
                $dt = new datetime($tasksObj->getinfo('dfinish'));
                $this->content.='<div style="width: fit-content">Завершена: '.$dt->format('d.m.Y H:i').'</button></div>';
            }
            $this->content.='<form style="width: fit-content" method="post" action="'.$this->selflink.'&edittask='.$taskId.'">';

            $this->content.='<div style="width: fit-content">Заголовок:<input class="form-control mb-2" type="text" name="header" value =" '.$itemInfo['header'].'"></div>';
            $this->content.='<div style="width: fit-content">Описание:<textarea class="form-control mb-2" name="description">'.$itemInfo['description'].'</textarea></div>';
            $this->content.='<div style="width: fit-content"><input type ="submit" name = "savetask" value ="Сохранить изменения" class="btn btn-success"></div>';
            $this->content.='</form>';


            if(isset($_GET['removeUser'])){
                $itemId=$_GET['removeUser'];

                if ($tasksusersObj->select($itemId)) {
                    $tasksusersObj->setinfo(['del'=>1]);
                }
                header('Location: '.$this->selflink);
            }

            $this->content.='<h3 style="width: fit-content">Назначить исполнителя</h3>';
            
            $userList = $tasksusersObj->getListBy((['taskid'=>$taskId]));

            foreach ($userList as $key => $value) {
                $itemId = $value['id'];
                $tasksusersObj->selectBy($value);

                $uid = $tasksusersObj-> getinfo('userid');
                $usersObj->select($uid);
                $uname = $usersObj->getinfo('name');
                $this->content .= '<div style="width: fit-content" class="d-flex d-flex align-items-center border border-1 w-25 ">' . '<div class="w-25 d-flex align-items-center">' . ' ' .'<span class="align-middle fw-bold text-primary">'. $uname .'</span>' . '</div>'. '<button class="btn btn-danger w-10 ms-2 my-2 ms-auto" onclick="window.location.href=\'' . $this->selflink . '&removeUser=' . $itemId . '\'">Удалить</button></div>';
            }

            $userList = $usersObj->getList();

            $opts='<option></option>';
            foreach ($userList as $key => $value) {
                $usersObj->select($value['id']);
                $opts.='<option value="'.$usersObj->getinfo('id').'">'.$usersObj->getinfo('name').'</option>';
            }
            
            if (isset($_POST['makeaduser'])) {
                $uid = $_POST['adduserid'];
                $rejectLink = $this->selflink;
                if ($tasksusersObj->create(['taskid'=>$taskId,'userid'=>$uid])) {
                    if ($tasksusersObj->selectBy(['taskid'=>$taskId,'userid'=>$uid])) {
                        $rejectLink.='&addsuccess';
                    }
                } else {
                   header('Location: '.$this->selflink.'');
                }
            }

        $this->content.='<form class="w-25" method="post" action="'.$this->selflink.'">';
        $this->content.='<div>Добавить исполнителя</div><div><select class="form-select my-2" name="adduserid">'.$opts.'</select></div>';
        $this->content.='<div><button class="btn btn-success my-2" type="submit" name="makeaduser">Подтвердить</button></div>';
        $this->content.='</form>';

         // Блок комментариев
        $this->content .= '<div class="row mt-4">
        <div class="col-md-6">'.$this->renderTaskEditor().'</div>
        <div class="col-md-6">'.$this->renderComments($taskId).'</div>
        </div>';
        $this->content.='<div "><a class="btn btn-sucsess" href="'.$baseLink.'"> <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M5 12l14 0" />
        <path d="M5 12l6 6" />
        <path d="M5 12l6 -6" />
      </svg>Назад</a></div>';
   

        } else {
            $this->content.='<div>Данные не найдены</div>';
            header('Location: /');
        }
    }

    private function renderTaskEditor() {
        // Возвращает HTML с текущей формой редактирования задачи
        ob_start();
        // ... текущий код формы редактирования ...
        return ob_get_clean();
    }

    private function renderComments($taskId) {
        $html = '<div style="margin-top: -530px;" class="task-comments">';
        $html .= '<h4>Комментарии к задаче</h4>';
        
        // Форма добавления комментария
        $html .= $this->getCommentForm($taskId);
        
        // Список комментариев
        $html .= $this->getCommentsList($taskId);
        
        $html .= '</div>';
        return $html;
    }

    private function getCommentForm($taskId) {
        return '
        <form method="post" action="'.$this->selflink.'" enctype="multipart/form-data" class="mb-4">
            <input type="hidden" name="task_id" value="'.$taskId.'">
            <div class="mb-3">
                <textarea name="comment" class="form-control" 
                    placeholder="Добавить комментарий..." required></textarea>
            </div>
            <button type="submit" name="add_comment" 
            class="btn btn-primary">Отправить</button>
            </form>';
        }
        // <div class="mb-3">
        // <input type="file" name="attachment" 
        //         accept="image/*, .doc, .docx, .pdf" 
        //         class="form-control">
        //     <small class="text-muted">Макс. размер: 5MB</small>
        // </div>
        
    private function getCommentsList($taskId) {
        $comments = $this->getTaskComments($taskId);
        $html = '<div class="comments-list">';
        
        foreach ($comments as $comment) {
            $messageObj = new Tmessages($this->dbcon);
            $messageObj->select($comment['id']);
            
            // Получаем данные пользователя
            $usersObj = new Tusers($this->dbconusers);
            $usersObj->select($messageObj->getinfo('senderId'));
            $userName = $usersObj->getinfo('name'); // Имя пользователя
    
            $html .= '
            <div class="comment card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="user-info">
                            <strong>' . $userName . '</strong>
                            <small class="text-muted">'
                            . date('d.m.Y H:i', strtotime($messageObj->getinfo('dcreate'))) . '</small>
                        </div>
                    </div>
                    <div class="comment-text">'
                    . nl2br(htmlspecialchars($messageObj->getinfo('descr'))) . '</div>';
    
            if (!empty($messageObj->getinfo('photo'))) {
                $html .= $this->renderAttachment($messageObj->getinfo('photo'));
            }
    
            $html .= '</div></div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    private function getTaskComments($taskId) {
        $messageObj = new Tmessages($this->dbcon);
        $comments = $messageObj->getListBy([
            'task_id' => $taskId,
            'type' => 'task_comment'
        ], ['order' => 'dcreate DESC']);
    
        // Отладка
        // echo "<pre>Comments for task $taskId: ";
        // print_r($comments);
        // echo "</pre>";
    
        return $comments;
    }

    private function renderAttachment($file) {
        $path = 'uploads/'.$file;
        if (strpos($file, '.pdf') !== false) {
            return '<a href="'.$path.'" target="_blank" class="badge bg-info">
                <i class="fas fa-file-pdf"></i> PDF</a>';
        }
        return '<img src="'.$path.'" class="comment-attachment img-thumbnail">';
    }

    function handleCommentSubmission() {
        if (isset($_POST['add_comment'])) {
            // echo "<pre>POST data: ";
            // print_r($_POST);
            // echo "</pre>";
    
            $taskId = (int)$_POST['task_id'];
            $userId = $_SESSION['userid'];
            $text = trim($_POST['comment']);
    
            $messageObj = new Tmessages($this->dbcon);
    
            $data = [
                'senderId' => $userId,
                'task_id' => $taskId,
                'descr' => $text,
                'type' => 'task_comment',
                'status' => 1,
                'dcreate' => date('Y-m-d H:i:s')
            ];
    
            // Отладка
            // echo "<pre>Comment data to save: ";
            // print_r($data);
            // echo "</pre>";
    
            if ($messageObj->create($data)) {
                header("Location: ?module=tasks&edittask=" . $taskId);
                exit;
            } else {
                $this->content .= '<div class="alert alert-danger">Ошибка сохранения комментария</div>';
            }
        }
    }

    function showTasks(){
        $tasksObj = new Ttasks($this->dbcon);
        $usersObj = new Tusers($this->dbconusers);
        $stateObj = new Tstate($this->dbcon);

        $this->content = '<h1>Задачи</h1>';
        $this->content .= '<div><a href="'.$this->selflink.'&addtask" class="btn btn-success mb-3">Добавить задачу</a></div>';

        if(isset($_GET['addtask'])) {
            $authObj = new CUserAuth($this->dbconusers);
            $tasksObj->create([
                'header' => 'Новая задача', 
                'owner' => $authObj->getUserId(),
                'state' => 0
            ]);
            header('Location: '.$this->selflink.'');
            exit;
        }

        if(isset($_GET['deletetask'])) {
            $uid = $_GET['deletetask'];
            if ($tasksObj->select($uid)) {
                $tasksObj->setinfo(['del' => 1]);
            }
            header('Location: '.$this->selflink.'');
            exit;
        }

        $tasks = $tasksObj->getList();
        $groupedTasks = [
            0 => ['title' => 'Созданные', 'tasks' => []],
            1 => ['title' => 'В работе', 'tasks' => []],
            2 => ['title' => 'Завершенные', 'tasks' => []]
        ];

        foreach ($tasks as $task) {
            $tasksObj->select($task['id']);
            $state = $tasksObj->getinfo('state');
            if(isset($groupedTasks[$state])) {
                $groupedTasks[$state]['tasks'][] = [
                    'id' => $task['id'],
                    'header' => $tasksObj->getinfo('header'),
                    'owner' => $tasksObj->getinfo('owner'),
                    'dcreate' => $tasksObj->getinfo('dcreate')
                ];
            }
        }

        $this->content .= '<div class="row">';
        foreach ($groupedTasks as $group) {
            $this->content .= '<div class="col-md-4 mb-4">';
            $this->content .= '<div class="border p-3 rounded">';
            $this->content .= '<h3 class="mb-3">'.$group['title'].'</h3>';
            
            foreach ($group['tasks'] as $task) {
                $usersObj->select($task['owner']);
                $dt = new DateTime($task['dcreate']);
                
                $this->content .= '<div class="card mb-3">';
                $this->content .= '<div class="card-body">';
                $this->content .= '<h5 class="card-title">'.$task['header'].'</h5>';
                $this->content .= '<p class="card-text">';
                $this->content .= '<small>Автор: '.$usersObj->getinfo('name').'</small><br>';
                $this->content .= '<small>Создана: '.$dt->format('d.m.Y H:i').'</small>';
                $this->content .= '</p>';
                $this->content .= '<div class="d-flex gap-2">';
                $this->content .= '<a href="'.$this->selflink.'&edittask='.$task['id'].'" class="btn btn-primary btn-sm">Открыть</a>';
                $this->content .= '<a href="'.$this->selflink.'&deletetask='.$task['id'].'" class="btn btn-danger btn-sm">Удалить</a>';
                $this->content .= '</div></div></div>';
            }
            
            $this->content .= '</div></div>';
        }
        $this->content .= '</div>';

        // Кнопка "Назад"
        $this->content .= '<div class="mt-4">';
        $this->content .= '<a href="?" class="btn btn-outline-secondary">';
        $this->content .= '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M5 12l14 0"/>
            <path d="M5 12l6 6"/>
            <path d="M5 12l6 -6"/>
          </svg> Назад</a>';
        $this->content .= '</div>';
    }
}