<?php

class Mmain extends MBaseModule {

    function execute()
    {
        $this->content='<h1>Main screen</h1>
        <div>
        <a href="?module=users">[Пользователи]</a>
        <a href="?module=tasks">[Задачи]</a>
        </div>';
        
    }
}