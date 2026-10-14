<?php
require_once "TokenManager.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type,authorization");

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $tokenManager = new TokenManager();

    $token = $tokenManager->getBearerToken();

    if($tokenManager->validate($token)){
        $userId = $tokenManager->extractUserID($token);
        $randomString = bin2hex(random_bytes(5));

        $sseAuthToken = createSSEAuthSignature($userId,$randomString);

        echo json_encode($userId.".".$randomString.".".$sseAuthToken);
    }
    else{
        echo json_encode(["error" => "401 Unauthorized"]);
    }

}

function createSSEAuthSignature($userId, $randomString){
    $signature = hash_hmac('sha512', $userId.$randomString, 'abc123');

    return $signature;
}
?>