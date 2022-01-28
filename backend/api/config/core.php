<?php

    include 'C:\wamp64\www\Digitera\orig-login-system\login-system-develop\backend\api\config\vendor\autoload.php';

    use Firebase\JWT\JWT;

    
    // load .env
    $dotenv = Dotenv\Dotenv::createImmutable('../');
    $dotenv->load();
    //show error reporting
    error_reporting(E_ALL);

    //set the default time-zone
    date_default_timezone_set('America/Toronto');

    //jwt
    $secret = $_ENV['SECRET'];
    $refresh_token = $_ENV['REFRESH_TOKEN'];
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 60); // valid for 1 hour
    $issuer = "http://localhost/Digitera";

    //email
    $email_username=$_ENV['EMAIL_USERNAME'];
    $email_pass=$_ENV['EMAIL_PASS'];
    $email_name=$_ENV['EMAIL_NAME'];

?>