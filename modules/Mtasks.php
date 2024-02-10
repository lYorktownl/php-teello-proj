<?php
class Mtasks extends MBaseModule {

    function execute()
    {
        if (isset($_GET['edittask'])) {
            $this->editTask();
        } else {
            $this->showTasks();
        }
    }

    function editTask(){
        $tasksObj = new Ttasks($this->dbcon);
        $this->content = '<h1>Редактирование задачи</h1>';
        $taskId = $_GET['edittask'];

        $usersObj = new Tusers($this->dbcon); 
        $stateObj = new Tstate($this->dbcon);

        if ($tasksObj->select($taskId)){
            if (isset($_POST['savetask'])){
                $newheader = $_POST['header'];
                $newdescription = $_POST['description'];
                if($tasksObj->setinfo(['header' => $newheader, 'description' => $newdescription])){
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
                $tasksObj->setinfo(['state'=>2, 'dfifnish'=>date('Y-m-d H:i:s')]);
                header('Location: '.$this->selflink.'');
            }

            $stateObj->select($tasksObj->getinfo('state'));
            $usersObj->select($tasksObj->getinfo('owner'));
            $dt = new datetime($tasksObj->getinfo('dcreate'));

            $this->content.='<div>Owner: '.$usersObj->getinfo('name').'</div>';
            $this->content.='<div>Created: '.$dt->format('d.m.Y H:i').'</div>';
            $this->content.='<div>State: '.$stateObj->getinfo('name').'</div>';
            if ($tasksObj->getinfo('state')==0) {
                $this->content.='<div><a href="'.$this->selflink.'&start"><button>Start</button></div>';
            }
            if ($tasksObj->getinfo('state')>0) {
                $dt = new datetime($tasksObj->getinfo('dcreate'));
                $this->content.='<div>In progress: '.$dt->format('d.m.Y H:i').'</div>';
            }
            if ($tasksObj->getinfo('state')==1) {
                $this->content.='<div><a href="'.$this->selflink.'&finish"><button>Finish</button></div>';
            }
            if ($tasksObj->getinfo('state')>1) {
                $dt = new datetime($tasksObj->getinfo('dfinish'));
                $this->content.='<div>Completed: '.$dt->format('d.m.Y H:i').'</button></div>';
            }
            $this->content.='<form method="post" action="'.$this->selflink.'">';
            $this->content.='<form method="post" action="'.$this->selflink.'">';
            $this->content.='<div>Заголовок:<input type ="text" name = "header" value =" '.$itemInfo['header'].'"></div>';
            $this->content.='<div>Описание:<textarea name="description">'.$itemInfo['description'].'</textarea></div>';
            $this->content.='<div><input type ="submit" name = "savetask" value ="Сохранить"></div>';
            $this->content.='</form>';

            $this->content.='<div><a href="/?module=tasks">Назад</div>';

        } else {
            $this->content.='<div>Данные не найдены</div>';
            header('Location: /');
        }
    }

    function showTasks(){
        $tasksObj = new Ttasks($this->dbcon);
        $usersObj = new Tusers($this->dbcon);
        $stateObj = new Tstate($this->dbcon);

        $this->content ='<h1>Задачи</h1>';
        $this->content.='<div><a href= "'.$this->selflink.'&addtask"> Добавить задачу</div>';

        if(isset($_GET['addtask'])){
            $authObj = new CUserAuth($this->dbcon);
            $tasksObj->create(['header'=>'Новая задача', 'owner'=>$authObj->getUserId()]);
            header('Location: '.$this->selflink);
        }
        if(isset($_GET['deletetask'])){
            $uid = $_GET['deletetask'];
            if ($tasksObj->select($uid)) {
                $tasksObj->setinfo(['del'=>1]);
            }
            header('Location: '.$this->selflink );
        }

        $userList = $tasksObj->getList();

        foreach ($userList as $key => $value) {
            $tasksObj->select($value['id']);
            $linkEdit = '<a href= '.$this->selflink. '&edittask='.$value['id'].'">[Редактировать]</a>';
            $linkDelete = '<a href= '.$this->selflink. '&deletetask='.$value['id'].'">[Удалить]</a>';
            $usersObj->select($tasksObj->getinfo('owner'));
            $stateObj->select($tasksObj->getinfo('state'));
            $dt = new datetime($tasksObj->getinfo('dcreate'));
            $this->content.='<div>'.$tasksObj->getinfo('owner').' 
            '.$tasksObj->getinfo('header').'
             ('.$dt->format('d.m.Y H:i').')
              '.$linkEdit.' '.$linkDelete.'</div>';
        }
        $this->content.='<div><a href="?">назад</div>';
    }
}