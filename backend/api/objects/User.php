<?php
    class User {
        // DB stuff
        private $conn;
        private $table = 'users';

        // Post Properties
        public $id;
        public $fullName;
        public $company;
        public $role = "client";
        public $username;
        public $country;
        public $contact;
        public $email;
        public $currentPlan;
        public $status;
        public $avatar;
        public $password;
        public $ability = array(array("action" => "manage", "subject" => "all"));
        public $extras = array("eCommerceCartItemsCount" => 5);
        public $error = null;
        
        // Constructor with DB
        public function __construct($db) {
         $this->conn = $db;  
        }
        
        
        //check if given email exists in the database
        function emailExists() {
            $query = "SELECT id, username, email, password
            FROM " . $this->table . "
            WHERE email=?
            LIMIT 0,1";
            
            $stmt = $this->conn->prepare($query);
            
            $this->email = htmlspecialchars(strip_tags($this->email));

            $stmt->bindParam(1, $this->email);
            
            $stmt->execute();
            
            $num = $stmt->rowCount();
            
            //if email exits, assign values to object properties for easay access and use for php session
            if($num>0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                //assign values to object properties
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->password=$row['password'];
                return true;
            }
            return false;
        }
        
        
        function resetTokenExists($token){
            $query = "SELECT id, username, email, password
            FROM " . $this->table . "
            WHERE reset_link_token=? AND exp_date > CURRENT_TIME
            LIMIT 0,1";
            
            $stmt = $this->conn->prepare($query);
            
            $this->email = htmlspecialchars(strip_tags($this->email));

            $stmt->bindParam(1, $token);
            
            $stmt->execute();
            
            $num = $stmt->rowCount();
            
            if($num>0) {
                return true;
            }
            return false;
        }
        
        
        function create() {

            //insert query
            $query = "INSERT INTO " . $this->table . "
                SET
                    username = :username,
                    fullname= :username,
                    email = :email,
                    password = :password";

            //prepare the query
            $stmt = $this->conn->prepare($query);

            //sanitize
            $this->username=htmlspecialchars(strip_tags($this->username));
            $this->email=htmlspecialchars(strip_tags($this->email));
            $this->password=htmlspecialchars(strip_tags($this->password));
            //hash the password before saving to database
            $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

            //bind the values
            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $password_hash);

            //execute the query, also check if query was successful
            try {
                $stmt->execute();
                $this->error = "";
                return true;
            }
            catch(PDOException $exception) {
                $this->error = $exception;
                return false;
            }
        }
        

       // Get Posts
       function login() {
           // Create query
           $query = "SELECT fullname, email FROM $this->table WHERE email='$this->email'";

           // Prepare statement
           $stmt = $this->conn->prepare($query);

           // Execute query
           $stmt->execute();

           return $stmt;
        }
        
        
        function forgotPassword($token, $expDate) {
            // Create query
           $query = "UPDATE $this->table SET reset_link_token='$token', exp_date='$expDate' WHERE email='$this->email'";

           // Prepare statement
           $stmt = $this->conn->prepare($query);

           // Execute query
           try {
                $stmt->execute();
                $this->error = "";
                return true;
            }
            catch(PDOException $exception) {
                $this->error = $exception;
                return false;
            }
        }
        
        
        function updatePassword($password) {
            //insert query
            $query = "UPDATE " . $this->table . " 
                SET
                    password = :password,
                    reset_link_token= null,
                    exp_date = null
                    WHERE email = :email";

            //prepare the query
            $stmt = $this->conn->prepare($query);

            //sanitize
            $this->email=htmlspecialchars(strip_tags($this->email));
            $this->password=htmlspecialchars(strip_tags($this->password));
            //hash the password before saving to database
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            //bind the values
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $password_hash);

            //execute the query, also check if query was successful
            try {
                $stmt->execute();
                $this->error = "";
                return true;
            }
            catch(PDOException $exception) {
                $this->error = $exception;
                return false;
            }
        }
    }