<?php 

class CoreApi {
    private $dbcon;
    private $dbconusers;
    private $authenticated;

    function __construct(){
         $this->authenticated = false;
    }

    function execute() {

    
        $connector = new CDBConnect;
        $this->dbcon = $connector->connect('connect.dat');
        $this->dbconusers = $connector->connect('connect2.dat');

        if(isset($_POST['request'])){ 
            $this->makeAuth();
            if ($_POST['request'] == 'getTasksList') {
                $this->checkAuth();
                if ($this->authenticated) {
                    $this->getTasksList();
                }
            } elseif ($_POST['request'] == 'getTaskData') {
                $this->checkAuth();
                if ($this->authenticated) {
                    $this->getTaskData();
                }
            }elseif ($_POST['request'] == 'setTaskData') {
                $this->checkAuth();
                if ($this->authenticated) {
                    $this->setTaskData();
                }

            }elseif ($_POST['request'] == 'setUserData') {
                $this->checkAuth();
                if ($this->authenticated) {
                    $this->setUserData();
                }

            }elseif ($_POST['request'] == 'setMessageData') {
                $this->checkAuth();
                if ($this->authenticated) {
                    $this->setMessageData();
                }          
            }elseif ($_POST['request'] == 'createNewTask') {
                $this->checkAuth();
                if ($this->authenticated) {
                    $this->createNewTask();
                }          
            }elseif ($_POST['request'] == 'createNewUser') {
                $this->checkAuth();
                if ($this->authenticated) {
                    $this->createNewUser();
                }          
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
    function setUserData() {
        $uid = stripcslashes($_POST['id']);
        $name = stripcslashes($_POST['name']);
        $email = stripcslashes($_POST['email']);
        $login = stripcslashes($_POST['login']);
        $password = stripcslashes($_POST['password']);
    
        // Check if task exists and is accessible by the user
        $usersObj = new Tusers($this->dbconusers);
        if ($usersObj->select($uid)) {
            $query = "UPDATE users SET name = '$name', email = '$email', login = '$login', password = '$password' WHERE id = '$uid'";
            if ($this->dbcon->query($query)) {
                print(json_encode(['success']));
            } else {
                print(json_encode(['update failed']));
            }
        } else {
            print(json_encode(['User not found']));
        }
    }
    function createNewUser() {

        $name = stripcslashes($_POST['name']);
        $email = stripcslashes($_POST['email']);
        $usersObj = new Tusers($this->dbconusers);
        $usersObj->create(['name'=>$name, 'email'=>$email]);
        if ($this->dbcon) {
            print(json_encode(['success']));
        } else {
            print(json_encode(['error' => 'Database insert failed']));
        }
    }

    function getTasksList (){
        $tasksObj = new Ttasks($this->dbcon);
        $tasksList = $tasksObj->getList();

        print(json_encode($tasksList)); 
    }

    function getTaskData () {
        $tasksObj = new Ttasks($this->dbcon);
        $taskid = stripcslashes($_POST['data']);

        if($tasksObj->select($taskid)){
            $taskData = [
                'id' => $taskid,
                'header' => $tasksObj->getinfo('header'),
                'description' => $tasksObj->getinfo('description'),      
            ];

            print(json_encode($taskData));
        }
    }

    function setTaskData() {
        $taskid = stripcslashes($_POST['taskid']);
        $title = stripcslashes($_POST['title']);
        $description = stripcslashes($_POST['description']);
    
        // Check if task exists and is accessible by the user
        $tasksObj = new Ttasks($this->dbcon);
        if ($tasksObj->select($taskid)) {
            $query = "UPDATE tasks SET header = '$title', description = '$description' WHERE id = '$taskid'";
            if ($this->dbcon->query($query)) {
                print(json_encode(['success']));
            } else {
                print(json_encode(['update failed']));
            }
        } else {
            print(json_encode(['Task not found']));
        }
    }
    function createNewTask() {

        $header = stripcslashes($_POST['header']);
        $description = stripcslashes($_POST['description']);
        $tasksObj = new Ttasks($this->dbcon);
        $authObj = new CUserAuth($this->dbconusers);
        $tasksObj->create(['header'=>$header, 'description'=>$description, 'owner'=>$authObj->getUserId()]);
        if ($this->dbcon) {
            print(json_encode(['success' => true, 'task_id' => $this->dbcon->insert_id]));
        } else {
            print(json_encode(['error' => 'Database insert failed']));
        }
    }

	function getMessagesList (){
        $messageObj = new Tmessages($this->dbcon);
        $messagesList = $messageObj->getList();

        print(json_encode($messagesList)); 
    }

    function getMessageData () {
        $messageObj = new Tmessages($this->dbcon);
        $messageId = stripcslashes($_POST['data']);

        if($messageObj->select($messageId)){
            $messageData = [
                'id' => $messageId,
                'title' => $messageObj->getinfo('title'),
                'descr' => $messageObj->getinfo('descr'),      
            ];

            print(json_encode($messageData));
        }
    }
    function setMessageData() {
        $messageId = stripcslashes($_POST['data']);
        $title = stripcslashes($_POST['title']);
        $descr = stripcslashes($_POST['descr']);
        
    
        // Check if task exists and is accessible by the user
        $messageObj = new Tmessages($this->dbcon);
        if ($messageObj->select($messageId)) {
            $query = "UPDATE messages SET title = '$title', descr = '$descr' WHERE id = '$messageId'";
            if ($this->dbcon->query($query)) {
                print(json_encode(['success']));
            } else {
                print(json_encode(['update failed']));
            }
        } else {
            print(json_encode(['Message not found']));
        }
    }

    function makeAuth (){
        $sesid='-1';
        $login = stripcslashes($_POST['login']);
        $password = stripcslashes($_POST['password']);

        $apisesObj = new Tapisession($this->dbcon);
        $usersObj = new Tusers($this->dbconusers);
        if ($usersObj->selectBy(['login'=>$login, 'password'=>md5($password)])) {
            $uid=$usersObj->getinfo('id');
            $dt = new datetime;
            $dt->modify('+1 day');
            if ($apisesObj->selectBy(['uid'=>$uid])) {
                $sesid= $apisesObj->getinfo('sesid');
                $rs = $apisesObj->setinfo(['datetill'=>$dt->format('Y-m-d H:i:s')]);
            }else{
                $sesid = md5($dt->format('Y-m-d H:i:s').$uid.rand(10,1000));
                $rs = $apisesObj->create(['uid'=>$uid, 'datetill'=>$dt->format('Y-m-d H:i:s'),'sesid'=>$sesid]);
            }
        }
        print (json_encode(['session_id'=>$sesid]));
    }

    function checkAuth(){
        if (isset($_POST['session_id'])) {
            $sesid = $_POST['session_id'];
        } 
        $apisesObj = new Tapisession($this->dbcon);
        
        if ($apisesObj->selectBy(['sesid' => $sesid])) {
            $this->authenticated = true; 
        } else {
            
            print(json_encode(['error' => 'Invalid session ID']));
            exit;
        }
    }


    function dbconnect (){
        $connector = new CDBConnect;
        $this->dbcon = $connector->connect('connect.dat');

        $stmt = $this->dbcon->query('SET NAMES utf8');
    }
}