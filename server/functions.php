<?php 
require "settings/db.php";
$query = 'SELECT * FROM users';
$result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

class Donate
{
    public $dbconn;
    function __construct() {
        $this->dbconn=pg_connect("host=localhost port=5432 dbname=donat user=public_hysteria password=0666");
    }
    
    public function makeDonation($amount){
        $check='SELECT * FROM users WHERE Login=$1 LIMIT 1';
        $authorized = pg_query_params($this->dbconn, $check, array($_GET['user']));
        $line = pg_fetch_assoc($authorized, 0);
       if(!$line){
            return 'Нет такого стримера:(';
        }else{
            if(!isset(($_POST['amount'])) || $_POST['amount']<1 || $_POST['amount']>1000000){
                return 'Введите адекватную сумму доната';
            }
            $res = pg_query_params($this->dbconn, 
            'INSERT INTO donats (Login, Amount, Date, Streamer_id, goal_id, Message) VALUES ($1, $2, $3, $4, $5, $6)',
            array($_POST['login'], $_POST['amount'], '2423', $line['id'], $line['goal_id'], $_POST['message']));
            
            $res = pg_query_params($this->dbconn, 
            'UPDATE users SET Balance = Balance+$1 WHERE id=$2',
            array($_POST['amount'], $line['id']));
            
            header('Location: /');
            
        }
    }
    public function listDonations($id){
        $check='SELECT * FROM users WHERE Id=$1 LIMIT 1';
        $authorized = pg_query_params($this->dbconn, $check, array($id));
        $line = pg_fetch_assoc($authorized, 0);
       if(!$line){
            return 'Нет такого стримера:(';
        }else{
            $users = pg_query_params($this->dbconn, 'SELECT * FROM donats where Streamer_id=$1 ORDER BY Id DESC LIMIT 5', array($id));
            return $users;
        }
    }
    public function listDonators($id){
        $check='SELECT * FROM users WHERE Id=$1 LIMIT 1';
        $authorized = pg_query_params($this->dbconn, $check, array($id));
        $line = pg_fetch_assoc($authorized, 0);
       if(!$line){
            return 'Нет такого стримера:(';
        }else{
            $users = pg_query_params($this->dbconn, 'SELECT * FROM donats where Streamer_id=$1 ORDER BY Login ', array($id));
            $dons=array();
            while($us=pg_fetch_assoc($users)){
                if(!isset($dons[$us['login']])){
                    $dons[$us['login']][0]=$us['amount'];
                    $dons[$us['login']][1]=$us['amount'];
                }
                if($dons[$us['login']][1]<$us['amount']){
                    $dons[$us['login']][1]=$us['amount'];
                }
                $dons[$us['login']][0]+=$us['amount'];
            }
            return $dons;
        }
    }
}

class Auth
{
    public $dbconn;
    public $secret;
    function __construct() {
        $this->dbconn=pg_connect("host=localhost port=5432 dbname=donat user=public_hysteria password=0666");
        $this->secret='i love pussy';
    }
    public function signup($name, $password)
    {
        if($this->checkCookie()){
            header('Location: /index.php');
        }else{
            $check='SELECT * FROM users WHERE Login=$1';
            $authorized = pg_query_params($this->dbconn, $check, array($name));
            $line = pg_fetch_array($authorized, null, PGSQL_ASSOC);
            $password = password_hash($password, PASSWORD_DEFAULT);
            if(!$line){
                $res = pg_query_params($this->dbconn, 'INSERT INTO users (Login, Password, Balance) VALUES ($1, $2, $3)', array($name, $password, 0));
                setcookie("user", password_hash($name, PASSWORD_DEFAULT), time()+3600); 
                header('Location: /index.php');
                return '';
            }else{
                return 'Имя уже занято';
            }
        }

    }
    public function signin($name, $password){
        if($this->checkCookie()){
            header('Location: /index.php');
        }else{
            $check='SELECT * FROM users WHERE Login=$1';
            $authorized = pg_query_params($this->dbconn, $check, array($name));
            $line = pg_fetch_array($authorized, null, PGSQL_ASSOC);
            if($line){
                $res = pg_query($this->dbconn, 'SELECT Password FROM users');
                while($resline = pg_fetch_array($res, null, PGSQL_ASSOC)){
                    foreach($resline as $result){
                        if(password_verify($password, $result)){
                            setcookie("user", password_hash($name, PASSWORD_DEFAULT), time()+3600); 
                            header('Location: /index.php');
                        }else{
                            return 'Неверный пароль';
                        }
                    }
                }
            }else{
                return "Неверный логин";
            }
        }
    }
    public function checkCookie(){
        if(isset($_COOKIE['user'])){
            $res = pg_query($this->dbconn, 'SELECT Login FROM users');
            while($resline = pg_fetch_array($res, null, PGSQL_ASSOC)){
                foreach($resline as $result){
                    if(password_verify($result, $_COOKIE['user'])){
                        return $result;
                    }
                }
            }
            return false;
        }else{
            return false;
        }
    }
    public function getUser(){
        if(isset($_COOKIE['user'])){
            $res = pg_query($this->dbconn, 'SELECT * FROM users');
            while($result = pg_fetch_assoc($res)){
                if(password_verify($result['login'], $_COOKIE['user'])){
                    return $result;
                }
                
            }
            return false;
        }else{
            return false;
        }
    }
    
}

?>