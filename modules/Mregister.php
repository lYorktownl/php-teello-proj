<?php
class Mregister extends MBaseModule {

    function execute() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegistration();
        } else {
            $this->showRegistrationForm();
        }
    }

    private function showRegistrationForm() {
        $this->content = file_get_contents('tabler-dev/demo/sign-up.html');
    }

    private function handleRegistration() {
        $name = trim($_POST['name']);
        $login = trim($_POST['login']);
        $password = trim($_POST['password']);
        $agree = isset($_POST['agree']) ? true : false;

        // Валидация данных
        if (empty($name) || empty($login) || empty($password)) {
            $this->content = '<div class="alert alert-danger">Все поля обязательны для заполнения.</div>';
            $this->showRegistrationForm();
            return;
        }

        if (!$agree) {
            $this->content = '<div class="alert alert-danger">Вы должны согласиться с политикой конфиденциальности.</div>';
            $this->showRegistrationForm();
            return;
        }

        // Проверка уникальности логина
        $usersObj = new Tusers($this->dbconusers);
        if ($usersObj->selectBy(['login' => $login])) {
            $this->content = '<div class="alert alert-danger">Пользователь с таким логином уже существует.</div>';
            $this->showRegistrationForm();
            return;
        }

        // Создание пользователя
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userId = $usersObj->create([
            'name' => $name,
            'login' => $login,
            'password' => $hashedPassword,
            // 'active' => 1
        ]);

        if ($userId) {
            $this->content = '<div class="alert alert-success">Регистрация прошла успешно! <a href="?">Войти</a></div>';
        } else {
            $this->content = '<div class="alert alert-danger">Ошибка при регистрации. Попробуйте еще раз.</div>';
            $this->showRegistrationForm();
        }
    }
}