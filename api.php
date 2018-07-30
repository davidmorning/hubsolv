<?php



// API class

class Api{
    private $con;
    private $host = "localhost";
    private $user = "root";
    private $pass = "password";
    private $database = "hubsolv";

    public function __construct($host = "", $user = "", $pass = "", $database = "")
    {
        if($host){
            $this->host = $host;
        }
        if($user){
            $this->user = $user;
        }
        if($pass){
            $this->pass = $pass;
        }
        if($database){
            $this->database = $database;
        }

        $this->con = mysqli_connect($this->host,$this->user,$this->pass,$this->database);

        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

    }

    public function post($post){
        if($this->validate($post['isbn'])){
            $this->insert($this->sanitize($post));
            $this->sendResponse(201, json_encode($post));
        }
    }

    public function get($get){
        $data = $this->query($this->sanitize($get));

        $this->sendResponse(200, json_encode($data));
    }

    protected function sanitize($post){
        $sanitized = array();

        foreach($post as $key => $data){
            $sanitized[$key] = mysqli_real_escape_string($data);
        }

        return $sanitized;
    }

    protected function query($data){

    }

    protected function insert($data){

    }

    protected function sendResponse($status, $json){
        http_response_code(201);
        echo http_response_code();
        echo $json;
    }

}

$api = new Api();

if($_POST){
    $api->post($_POST);
}

if($_GET){
    $api->get($_GET);
}