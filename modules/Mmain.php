<?php

class Mmain extends MBaseModule {

    function execute()
    {
        $this->content='<h1>Main screen</h1>
        <div>
        <a href="?module=users" class="btn btn-primary">Пользователи</a>
        <a href="?module=tasks"class="btn btn-primary">Задачи</a>
        </div>';
        
    }
}