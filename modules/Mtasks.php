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
        $usersObj = new Tusers($this->dbcon); 
        $stateObj = new Tstate($this->dbcon);
        $tasksusersObj = new Ttasksusers($this->dbcon);

        $this->content = '<h1>Редактирование задачи</h1>';
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

            $this->content.='<div>Owner: '.$usersObj->getinfo('name').'</div>';
            $this->content.='<div>Created: '.$dt->format('d.m.Y H:i').'</div>';
            $this->content.='<div>State: '.$stateObj->getinfo('name').'</div>';
            if ($tasksObj->getinfo('state')==0) {
                $this->content.='<div><a href="'.$this->selflink.'&edittask='.$taskId.'&start"><button>Start</button></a></div>';
                
            }
            if ($tasksObj->getinfo('state')>0) {
                $dt = new datetime($tasksObj->getinfo('dcreate'));
                $this->content.='<div>In progress: '.$dt->format('d.m.Y H:i').'</div>';
            }
            if ($tasksObj->getinfo('state')==1) {
                $this->content.='<div><a href="'.$this->selflink.'&edittask='.$taskId.'&finish"><button>Finish</button></a></div>';
            }
            if ($tasksObj->getinfo('state')>1) {
                $dt = new datetime($tasksObj->getinfo('dfinish'));
                $this->content.='<div>Completed: '.$dt->format('d.m.Y H:i').'</button></div>';
            }
            $this->content.='<form method="post" action="'.$this->selflink.'&edittask='.$taskId.'">';

            $this->content.='<div>Заголовок:<input type="text" name="header" value =" '.$itemInfo['header'].'"></div>';
            $this->content.='<div>Описание:<textarea name="description">'.$itemInfo['description'].'</textarea></div>';
            $this->content.='<div><input type ="submit" name = "savetask" value ="Сохранить"></div>';
            $this->content.='</form>';


            if(isset($_GET['removeUser'])){
                $itemId=$_GET['removeUser'];

                if ($tasksusersObj->select($itemId)) {
                    $tasksusersObj->setinfo(['del'=>1]);
                }
                header('Location: '.$this->selflink);
            }

            $this->content.='<h3>employees</h3>';
            $userList = $tasksusersObj->getListBy((['taskid'=>$taskId]));

            foreach ($userList as $key => $value) {
                $itemId = $value['id'];
                $tasksusersObj->selectBy($value);

                $uid = $tasksusersObj-> getinfo('userid');
                $usersObj->select($uid);
                $uname = $usersObj->getinfo('name');
                $this->content.='<div>'.$uid.' '.$uname.'<a href="'.$this->selflink.'&removeUser='.$itemId.'">[Delete]</a></div>';
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

        $this->content.='<form method="post" action="'.$this->selflink.'">';
        $this->content.='<div>Add member</div><div><select name="adduserid">'.$opts.'</select></div>';
        $this->content.='<div><input type="submit" name="makeaduser" value="Add"></div>';
        $this->content.='</form>';


        $this->content.='<div><a href="'.$baseLink.'">Назад</a></div>';
   

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
        $this->content.='<div><a href="'.$this->selflink.'&addtask"><button>Добавить задачу</button></a></div>';

        if(isset($_GET['addtask'])){
            $authObj = new CUserAuth($this->dbcon);
            $tasksObj->create(['header'=>'Новая задача', 'owner'=>$authObj->getUserId()]);
            header('Location: '.$this->selflink.'');
        }
        if(isset($_GET['deletetask'])){
            $uid = $_GET['deletetask'];
            if ($tasksObj->select($uid)) {
                $tasksObj->setinfo(['del'=>1]);
            }
            header('Location: '.$this->selflink.'' );
        }

        $userList = $tasksObj->getList();

        foreach ($userList as $key => $value) {
            $tasksObj->select($value['id']);
            $linkEdit = '<a href="'.$this->selflink.'&edittask='.$value['id'].'">[Редактировать]</a>';
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