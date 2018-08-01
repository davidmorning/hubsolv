<?php

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

    // Handling people creating records
    public function post($post)
    {
        $parsed = json_decode($post, true);
        $data = $parsed['data'];

        if ($parsed['query_type'] = 'insert') {
            if ($this->validateIsbn($data['isbn'])) {
                try {
                    $this->insert($data);
                } catch (Exception $e) {
                    // Exception handling here for failed query
                    die("An error occurred with the query");
                }

                $this->sendResponse(201, json_encode($data));
            } else {
                $this->sendResponse(400, "Invalid ISBN");
            }
        }elseif($parsed['query_type'] = 'query'){
            $result = $this->query(
                $this->sanitize(json_decode($data))
            );

            $this->sendResponse(200, json_encode($result));
        }
    }

    // Check ISBN has either 10 or 13 characters (both apparently valid) with any non-numeric characters stripped
    protected function validateIsbn($isbn){
        $stripped = preg_replace("/[^0-9]/", "", $isbn );
        if(strlen($stripped) == 13 or strlen($stripped) == 10){
            return true;
        }else{
            return false;
        }
    }

    // Sanitize data before it's passed to a query
    protected function sanitize($raw){
        $sanitized = array();

        foreach($raw as $key => $data){
            $sanitized[$key] = mysqli_real_escape_string($this->con, $data);
        }

        return $sanitized;
    }

    // Request data
    protected function query($data){
        $query = sprintf("select * from Books where %s", $this->parseQueryData($data));
        $result = mysqli_query($this->con, $query);
        return mysqli_fetch_assoc($result);
    }

    // Insert record
    protected function insert($data){
        $sanitized = $this->sanitize($data);
        if($this->validateData($sanitized)) {

            $query = sprintf(
                "insert into Books values ('%s', '%s', '%s', '%s', '%s')",
                $sanitized['isbn'],
                $sanitized['title'],
                $sanitized['author'],
                $sanitized['category'],
                $sanitized['price']
            );
            try {
                mysqli_query($this->con, $query);
            }catch(Exception $e){
                // Some kind of error handling should go here, but it's not specified in the documentation
                die(mysqli_error($this->con));
            }
        }else {
            // Some kind of error handling should go here, but it's not specified in the documentation
            die("Data failed validation");
        }
    }

    // Check all data is available
    protected function validateData($data){
        if(
            isset($data['isbn']) and
            isset($data['title']) and
            isset($data['author']) and
            isset($data['category']) and
            isset($data['price'])
        ){
            return true;
        }else{
            return false;
        }
    }

    // Send the response to the user
    protected function sendResponse($status, $json){
        http_response_code($status);
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


// Test Data
$testData = json_encode(Array(
        'query_type' => "insert",
        'data' => Array(
            'isbn' => "1232567890",
            'title' => "This is a test book",
            'author' => "Random Author",
            'category' => "none",
            'price' => "9.99"
        )
    )
);
// Testing
$api->post($testData);