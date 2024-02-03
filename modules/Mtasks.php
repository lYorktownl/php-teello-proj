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

        if ($tasksObj->select($taskId)){
            if (isset($_POST['savetask'])){
                $newtitle = $_POST['title'];
                $newdescription = $_POST['description'];
                if($tasksObj->setinfo(['title' => $newtitle, 'description' => $newdescription])){
                    header('Location: /?edittask=' .$taskId.'&savesuccess');
                } else {
                    $this->content.='<div class="errors">Ошибка сохранения</div>';
                }
            }

            $this->content.='<form method="post">';
            $this->content.='<div><label for="title">Заголовок:</label><input type ="text" name = "title" value =" '.$tasksObj->getinfo('title').'"></div>';
            $this->content.='<div><label for="description">Описание:</label><textarea name="description">'.$tasksObj->getinfo('description').'</textarea></div>';
            $this->content.='<div><input type ="submit" name = "savetask" value ="Сохранить"></div>';
            $this->content.='</form>';

            $this->content.='<div><a href="/">Назад</div>';

        } else {
            $this->content.='<div>Данные не найдены</div>';
            header('Location: /');
        }
    }

    function showTasks(){
        $tasksObj = new Ttasks($this->dbcon);

        $this->content ='<h1>Задачи</h1>';
        $this->content.='<div><a href= "?addtask"> Добавить задачу</div>';

        if(isset($_GET['addtask'])){
            $tasksObj->create(['title'=>'Новая задача']);
            header('Location: /');
        }
        if(isset($_GET['deletetask'])){
            $tid = $_GET['deletetask'];
            if ($tasksObj->select($tid)) {
                $tasksObj->setinfo(['del'=>1]);
            }
            header('Location: /');
        }

        $taskList = $tasksObj->getList();

        foreach ($taskList as $key => $value) {
            $tasksObj->select($value['id']);
            $linkEdit = '<a href= "?edittask='.$value['id'].'">[Редактировать]</a>';
            $linkDelete = '<a href= "?deletetask='.$value['id'].'">[Удалить]</a>';
            $this->content.='<div>'.$tasksObj->getinfo('title').' '.$linkEdit.' '.$linkDelete.'</div>';
        }
    }
}