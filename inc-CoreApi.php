<?php 

class CoreApi {
    private $content;
    private $tmpl;
    private $dbcon;
    private $dbconusers;
    private $userName;

    function __construct(){
        
      
    }
    function execute() {
        $connector= new CDBConnect;
        $this->dbcon = $connector->connect('connect.dat');

        $this->dbconusers = $connector->connect('connect2.dat');

        // print(json_encode(['text access']));

        if(isset($_POST['request'])){
            if ($_POST['request']=='getUserList') {
                $this->getUserList();
            }elseif ($_POST['request']=='getUserData') {
                $this->getUserData();
            }
        }
    }
    function getUserList (){
        $usersObj = new Tusers($this->dbconusers);
        $uList = $usersObj->getList();

      print(json_encode($uList));
    }

    function getUserData (){
        $usersObj = new Tusers($this->dbconusers);
        $uid = stripcslashes($_POST['data']);
        if($usersObj->select($uid)){
        $userData = [
                'id' => $uid,
                'name' => $usersObj->getinfo('name'),
                'email' => $usersObj->getinfo('email'),      
            ];

            print(json_encode($userData));
        }
    }
	

    function dbconnect (){
        $connector = new CDBConnect;
        $this->dbcon = $connector->connect('connect.dat');

        $stmt = $this->dbcon->query('SET NAMES utf8');
    }

  
}