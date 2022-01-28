<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    // files needed to connect to database
    include_once 'config/vendor/autoload.php';
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

    // get posted data
    $postedData = preg_replace('/\s+/', '', file_get_contents("php://input"));
    $data = json_decode($postedData, true );

    // set user property values
    $user->username = $data['username'];
    $user->email = $data['email'];
    $user->fullName = $data['username'];
    $user->password = $data['password'];

    //set default errors
    $emailError = null;
    $usernameError = null;

    //create the user
    if(
        !empty($user->username) &&
        !empty($user->email) &&
        !empty($user->password) &&
        $user->create()
    ) {
        $token = array(
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "iss" => $issuer,
            "data" => array(
                "id" => $user->id,
                "email" => $user->email,
            )
        );

        $accessToken = JWT::encode($token, $secret);
        $refreshToken = "";

        //set response code
        http_response_code(200);

        //display message: user was created
         echo json_encode(
            array(
                "message" => ["User was created."],
                "userData" => $user,
                "accessToken" => $accessToken, 
                "refreshToken" => $refreshToken
                )    
        );
    }

    //message if unable to create user
    elseif ($user->error->errorInfo) {

        if(strpos($user->error->errorInfo[2], "email"))
            $emailError = "This email is already in use.";
        if(strpos($user->error->errorInfo[2], "username"))
            $usernameError = "This username is already in use.";

        //set response code
        http_response_code(400);
        //display message: unable to create user
        echo json_encode(array("error" => array(
                                        "email" => $emailError,
                                        "username" => $usernameError
                                    )
                                )  
                        );
    }

?>

