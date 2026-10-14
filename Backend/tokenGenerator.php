<?php
require_once "TokenManager.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: userId");

if($_SERVER["REQUEST_METHOD"] === "GET"){
    $get = getallheaders();
    $tokenManager = new TokenManager();
    $token = $tokenManager->generateToken($get["userId"]);
    echo json_encode($token);
}

?>