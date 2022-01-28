<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    // files needed to connect to database
    include_once 'config/database.php';
    include_once 'objects/user.php';

    // files needed to use phpmailer
    use PHPMailer\PHPMailer\PHPMailer; 
    use PHPMailer\PHPMailer\Exception; 
    require_once('config/vendor/phpmailer/phpmailer/src/PHPMailer.php'); 
    require_once('config/vendor/phpmailer/phpmailer/src/Exception.php');
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

    if(isset($data['email']) && $data['password'] && $data['token']){     
        
        $user->email=$data['email'];
        
        if($user->emailExists() && $user->resetTokenExists($data['token']) && $user->updatePassword($data['password'])) {
            //set response code
            http_response_code(200);
            
            echo json_encode(
                 array(
                     "message" => 'Password is reset successfully.',
                 )
             );
        }
        else {
            //set response code
            http_response_code(400);
            
             echo json_encode(array("error" => "The link has been expired."));
        }
    } 
    else {
        echo 'please provide a valid password';
    }
?>