<?php

class Mmain extends MBaseModule {

    function execute()
    {
        $tasksObj = new Ttasks($this->dbcon);
        $usersObj = new Tusers($this->dbconusers);
        $authObj = new CUserAuth($this->dbconusers);

        $this->content = '<h1>Главный экран</h1>';

        // Приветствие
        $this->content .= '<div>Добро пожаловать, ' . '{[user_name]}' . '!</div>';
        

        // Статистика
        $totalTasks = $tasksObj->getCount();
        //$activeUsers = $usersObj->getCount(['active' => 1]);
        $this->content .= '<div class="stats mb-4">
            <div>Всего задач: ' . $totalTasks . '</div>
            
        </div>';

        // Быстрые действия
        $this->content .= '<div class="quick-actions mb-4">
            <a href="?module=tasks&addtask" class="btn btn-success">Создать задачу</a>
            <a href="?module=users&adduser" class="btn btn-success">Добавить пользователя</a>
        </div>';

        // Последние задачи
        $recentTasks = $tasksObj->getList(['order' => 'dcreate DESC', 'limit' => 5]);
        $this->content .= '<h3>Последние задачи</h3>';
        foreach ($recentTasks as $task) {
            $tasksObj->select($task['id']);
            $dt = new DateTime($tasksObj->getinfo('dcreate'));
            $this->content .= '<div class="card p-2 my-2 w-50">' . $tasksObj->getinfo('header') . ' (создана: ' . $dt->format('d.m.Y H:i') . ')</div>';
        }
    }
}