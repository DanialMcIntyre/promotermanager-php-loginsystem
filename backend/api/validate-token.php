<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type");

    // files needed to connect to database
    include_once 'config/database.php';
    include_once 'objects/user.php';

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

    // get the posted data
    $postedData = preg_replace('/\s+/', '', file_get_contents("php://input"));
    $data = json_decode($postedData,true);

    // get the refresh token
    $token = $data['refreshToken'];
    echo $token;
return;
    try {
        $jwt = JWT::decode($token, $refresh_token, array('HS256'));
    } catch(Exception $e) {
        echo "Error";
        return;
    }
    $user->email = $jwt->data->email;

    if($user->emailExists()) {
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
    } else {
        http_response_code(400);
        echo 'Login failed.';
    }

    