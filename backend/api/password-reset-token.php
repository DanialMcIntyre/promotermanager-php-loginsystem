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

    if(isset($data['email'])){
        $user->email=$data['email'];
        $mail = new PHPMailer();
        $token = md5($user->email).rand(10,9999);
        $expFormat = mktime(
             date("H")+1, date("i"), date("s"), date("m") ,date("d"), date("Y")
             );

        $expDate = date("Y-m-d H:i:s",$expFormat);
        
        if($user->emailExists() && $user->forgotPassword($token,$expDate)) {
            $link = "<a href='http://localhost:8080/reset-password?email=".$user->email."&token=".$token."'>Click To Reset password</a>";
            
            $mail->CharSet =  "utf-8";
            $mail->IsSMTP();
            // enable SMTP authentication
            $mail->SMTPAuth = true;                  
            // GMAIL username
            $mail->Username = $email_username;
            // GMAIL password
            $mail->Password = $email_pass;
            $mail->SMTPSecure = "ssl";  
            // sets GMAIL as the SMTP server
            $mail->Host = "smtp.gmail.com";
            // set the SMTP port for the GMAIL server
            $mail->Port = "465";
            $mail->From=$email_username;
            $mail->FromName=$email_name;
            $mail->AddAddress($data['email'], 'test');
            $mail->Subject  =  'Reset Password';
            $mail->IsHTML(true);
            $mail->Body    = 'Click On This Link to Reset Password '.$link.'';
            if($mail->Send())
            {
                //set response code
                http_response_code(200);
                echo json_encode(
                 array(
                     "message" => 'Reset link sent to your email',
                    )
                );
            }
            else {
                http_response_code(400);
                echo json_encode(array("error" => "Error"));
            }
        }
        else {
            http_response_code(400);
            echo json_encode(array("error" => "Error"));
        }
    } 
    else {
       http_response_code(200);
        echo json_encode(
            array(
                 "message" => 'Reset link sent to your email',
             )
        );;
    }
?>