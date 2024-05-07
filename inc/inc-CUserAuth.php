<?php

class CUserAuth {
    private $dbconusers;
    private $auth = 0;
    private $uid =0;
    function __construct($con)
    {
        $this->dbconusers =$con;
       
        if(isset($_POST['makelogin'])){

            $login=$_POST['login'];
            $password=$_POST['password'];
            $userObj = new Tusers($this->dbconusers);
            if ($userObj->selectBy(['login'=>$login])) {
                $_SESSION['userid']=$userObj->getinfo('id');
            }
            
        }
        
        if (isset($_GET['logout'])) {
            unset($_SESSION['userid']);
            header('Location: ?');
        }

        if (isset($_SESSION['userid'])) {
            $this->auth =1;
            $this->uid =$_SESSION['userid'];
        }
        
    }

    function getUserId(){
        return $this->uid;
    }

    function checkAuth (){
        return $this->auth;
    }
    
}