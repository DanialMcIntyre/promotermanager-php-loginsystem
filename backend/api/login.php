<?php

    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    
        return 0;    
     }    

    // files needed to connect to database
    include_once 'config/database.php';
    include_once 'objects/user.php';

    require 'config/vendor/autoload.php';

    // generate json web token
    include_once 'config/core.php';
    include_once 'config/vendor/firebase/php-jwt/src/BeforeValidException.php';
    include_once 'config/vendor/firebase/php-jwt/src/ExpiredException.php';
    include_once 'config/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
    include_once 'config/vendor/firebase/php-jwt/src/JWT.php';
    use \Firebase\JWT\JWT;
        
    // get database connection
    $database = new Database();
    $db = $database->connect();

    // instantiate user object
    $user = new User($db);

    // get posted data
    $postedData = preg_replace('/\s+/', '', file_get_contents("php://input"));
    $data = json_decode($postedData, true );
    // set user property values
    $user->email = $data['email'];
    $email_exists = $user->emailExists();

    if($email_exists && password_verify($data['password'], $user->password)) {

            $token = array(
                "iat" => $issued_at,
                "exp" => $expiration_time,
                "iss" => $issuer,
                "data" => array(
                    "id" => $user->id,
                    "email" => $user->email,
                )
            );

            //set response code
            http_response_code(200);

            //generate jwt
            $accessToken = JWT::encode($token, $secret);
            $refreshToken = JWT::encode($token, $refresh_token);

            unset($user->password);

            //display message: user was created
             echo json_encode(
                 array(
                     "message" => ["Successful login."],
                     "userData" => $user,
                     "accessToken" => $accessToken,
                     "refreshToken" => $refreshToken
                 )
             );
    }

    //login failed
    else {
        $user->error = "Email or Password is Invalid!";
        //set response code
        http_response_code(400);
        //tell the user login failed
        echo json_encode(array("error" => array(
                                        "email" => $user->error)));
    }
?>